<?php

class opActivityDataConverter
{
  static public function activityToStatus(ActivityData $activity)
  {
    return array(
      'created_at' => $activity->getCreatedAt(),
      'id' => $activity->getId(),
      'text' => $activity->getBody(),
      'user' => self::memberToUser($activity->getMember())
    );
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
