<?php

class saaActions extends sfActions
{
  public function forward404($message = null)
  {
    $this->forward('saa', 'error404');
  }

  protected function render($data)
  {
    $format = $this->getRequest()->getParameter('format', 'json');
    $class = 'opSaaExport'.ucfirst(strtolower($format));
    $this->forward404Unless(class_exists($class));
    $exportClass = new $class;
    $this->getResponse()->setContentType($exportClass->getContentType());
    return $this->renderText($exportClass->export($data));
  }

  protected function validate($validators, $default = array())
  {
    $result = array();
    try
    {
      foreach ($validators as $name => $validator)
      {
        $value = $this->getRequest()->getParameter($name, isset($default[$name]) ? $default[$name] : null);
        $result[$name] = $validator->clean($value);
      }
    }
    catch (sfValidatorError $e)
    {
      $this->forward('saa', 'error403');
    }
    return $result;
  }

  protected function getMemberIdByBasic()
  {
    if (!isset($_SERVER['PHP_AUTH_USER']))
    {
      $this->forward('saa', 'basicAuth');
    }
    else
    {
      $email = $_SERVER['PHP_AUTH_USER'];
      $password = md5($_SERVER['PHP_AUTH_PW']);
      $memberConfig = Doctrine::getTable('MemberConfig')->retrieveByNameAndValue('pc_address', $email);
      if ($memberConfig)
      {
        $data = $memberConfig->getMember()->getConfig('password');
        if ($data === $password)
        {
          return $memberConfig->getMemberId();
        }
      }
      $this->forward('saa', 'basicAuth');
    }
  }

  protected function getMemberIdByOAuth()
  {
    require_once 'OAuth.php';
    $consumer = $token = null;

    try
    {
      $req = OAuthRequest::from_request();
      list($consumer, $token) = $this->getServer()->verify_request($req);
    }
    catch (OAuthException $e)
    {
      // do nothing
    }

    if ($consumer)
    {
      $information = Doctrine::getTable('OAuthConsumerInformation')->findByKeyString($consumer->key);
      if ($information)
      {
        $tokenType = $this->getRequest()->getParameter('token_type', 'member');
        if ('member' === $tokenType)
        {
          $accessToken = Doctrine::getTable('OAuthMemberToken')->findByKeyString($token->key, 'access');
          return $accessToken->getMemberId();
        }
      }
    }

    $this->forward('saa', 'error401');
  }

  protected function getMemberId()
  {
    if ($this->getRequest()->hasParameter('oauth_token'))
    {
      return $this->getMemberIdByOAuth();
    }
    if (sfConfig::get('op_saa_use_basic_auth', false))
    {
      return $this->getMemberIdByBasic();
    }
    $this->forward('saa', 'error401');
  }

  public function executeStatusesHomeTimeline(sfWebRequest $request)
  {
    $memberId = $this->getMemberId();
    $validators = array(
      'since_id'         => new sfValidatorInteger(array('required' => false, 'min' => 1)),
      'max_id'           => new sfValidatorInteger(array('required' => false, 'min' => 1)),
      'count'            => new sfValidatorInteger(array('required' => false, 'max' => 200, 'min' => 1)),
      'page'             => new sfValidatorInteger(array('required' => false, 'min' => 1)),
      'term_user'        => new sfValidatorBoolean(array('required' => false)),
    );

    $default = array(
      'count'            => 20,
      'page'             => 1,
      'term_user'        => false
    );

    $params = $this->validate($validators, $default);

    $q = Doctrine::getTable('ActivityData')->createQuery();
    $dql = 'member_id = ?';
    $dqlParams = array($memberId);
    $friendIds = Doctrine::getTable('MemberRelationship')->getFriendMemberIds($memberId);
    $flags = Doctrine::getTable('ActivityData')->getViewablePublicFlags(ActivityDataTable::PUBLIC_FLAG_FRIEND);
    if ($friendIds)
    {
      $query = new Doctrine_Query();
      $query->andWhereIn('member_id', $friendIds);
      $query->andWhereIn('public_flag', $flags);

      $dql .= ' OR '.implode(' ', $query->getDqlPart('where'));
      $dqlParams = array_merge($dqlParams, $friendIds, $flags);
    }
    $q->andWhere('('.$dql.')', $dqlParams);
    $q->andWhere('in_reply_to_activity_id IS NULL');

    $q->limit($params['count']);
    if ($params['since_id'])
    {
      $q->andWhere('id > ?', $params['since_id']);
    }
    if ($params['max_id'])
    {
      $q->andWhere('id <= ?', $params['max_id']);
    }

    if (1 !== $params['page'])
    {
      $q->offset(($params['page'] - 1) * $params['count']);
    }

    $activities = $q->orderBy('id DESC')->execute();
    $statuses = array();
    foreach ($activities as $activity)
    {
      $statuses[] = array('status' => opActivityDataConverter::activityToStatus($activity, $params['term_user']));
    }

    return $this->render(array('statuses' => $statuses));
  }

  public function executeStatusesUpdate(sfWebRequest $request)
  {
    $memberId = $this->getMemberId();
    $validators = array(
      'status' => new opValidatorString(array('required' => true, 'trim' => true, 'max_length' => 140)),
    );
    $params = $this->validate($validators);
    $activity = Doctrine::getTable('ActivityData')->updateActivity($memberId, $params['status']);

    return $this->render(array('status' => opActivityDataConverter::activityToStatus($activity)));
  }


  public function executeBasicAuth(sfWebRequest $request)
  {
    $response = $this->getResponse();
    $response->setHttpHeader('WWW-Authenticate', 'Basic realm="Please enter your address and password"');
    $response->setStatusCode(401);
    return $this->renderText('401 Unauthorized');
  }

  public function executeError401(sfWebRequest $request)
  {
    $this->getResponse()->setStatusCode(401);
    return $this->renderText('401 Unauthorized');
  }

  public function executeError403(sfWebRequest $request)
  {
    $this->getResponse()->setStatusCode(403);
    return $this->renderText('403 Forbidden');
  }

  public function executeError404(sfWebRequest $request)
  {
    $this->getResponse()->setStatusCode(404);
    return $this->renderText('404 Not Found');
  }
}
