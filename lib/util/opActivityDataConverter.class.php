<?php

class opActivityDataConverter
{
  static public function activityToStatus(ActivityData $activity, $isTermUser = false)
  {
    $result = array(
      'created_at' => $activity->getCreatedAt(),
      'id' => $activity->getId(),
      'text' => $activity->getBody(),
    );
    if ($isTermUser)
    {
      $result['user'] = array('id' => $activity->getMemberId());
    }
    else
    {
      $result['user'] = self::memberToUser($activity->getMember());
    }

    return $result;
  }

  static public function memberToUser(Member $member)
  {
    return array(
      'id' => $member->getId(),
      'name' => $member->getName(),
      'screen_name' => $member->getName()
    );
  }
}
