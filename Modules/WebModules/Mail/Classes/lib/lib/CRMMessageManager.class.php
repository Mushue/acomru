<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CRMMessageManager
 *
 * @author p.gorbachev
 */
class CRMMessageManager
{

    const
        EVT_TAG_POST = 'PostGotTag',
        EVT_POST_TAGS_RM = 'PostTagsRm',
        EVT_CONTENT_ACCEPTED = 'ProjectUserContentPublished',
        EVT_CONTENT_REJECTED = 'ProjectUserContentRejected',
        EVT_WINNER_ASSIGNED = 'WinnerAssigned',
        EVT_POST_REJECTED = 'PostRejected',
        EVT_POST_GOT_LIKE = 'PostGotLike',
        EVT_POST_GOT_COMMENT = 'PostGotComment',
        EVT_USER_VOTED = 'UserVotedInContest',
        EVT_USER_GOES_TO_EVT = 'UserGoesToEvent',
        EVT_FOLLOW = 'Follow',
        EVT_USER_PROP_RM = 'DeleteUserProperty',
        EVT_USER_PROP_ADD = 'AddUserProperty',
        EVT_PARTY_IMG_COMMENT = 'PartyImageGotComment',
        EVT_PARTY_IMG_LIKE = 'PartyImageGotLike',
        EVT_PARTY_VIDEO_LIKE = 'PartyVideoGotLike',
        EVT_PARTY_VIDEO_COMMENT = 'PartyVideoGotComment',
        EVT_CONTEST_COMMENT = 'ContestGotComment',
        EVT_POINTS_ADDED = 'PointsAdded',
        EVT_PARTY_NOTIFY = 'PartyNotify',
        EVT_CONTENT_LOOSE = 'ProjectUserContentLoose',
        EVT_POST_GOT_COMMENT_NOTIFICATION = 'PostGotCommentNotification',
        EVT_CODE_ACTIVATION_BY_PHONE = 'CodeActivationByPhone',
        EVT_USER_REGISTERED = 'UserRegistered',
        EVT_CODE_TRY = 'CodeTry',
        EVT_CODE_SUCCESS = 'CodeSuccess',
        EVT_CODE_ALREADY_REGISTERED = 'CodeAlreadyRegistered',
        EVT_CODE_NOT_EXISTS = 'CodeDoesNotExist',
        EVT_CODE_VALIDATION_FAILED = 'CodeRegistrationFailedValidation',
        EVT_POINTS_ADDED_MMP = 'PointsAddedMmp',
        EVT_PRIZE_ASSIGNED_MMP = 'PrizeAssignedMmp',
        EVT_PRIZE_ORDERED_MMP = 'PrizeOrderedMmp';

    protected $routingKeys = array(
        self::EVT_TAG_POST => 'PostGotTag',
        self::EVT_POST_TAGS_RM => 'PostTagsRm',
        self::EVT_CONTENT_ACCEPTED => 'ProjectUserContentPublished',
        self::EVT_CONTENT_REJECTED => 'ProjectUserContentRejected',
        self::EVT_WINNER_ASSIGNED => 'WinnerAssigned',
        self::EVT_POST_REJECTED => 'PostRejected',
        self::EVT_POST_GOT_LIKE => 'PostGotLike',
        self::EVT_POST_GOT_COMMENT => 'PostGotComment',
        self::EVT_USER_VOTED => 'UserVotedInContest',
        self::EVT_USER_GOES_TO_EVT => 'UserGoesToEvent',
        self::EVT_FOLLOW => 'Follow',
        self::EVT_USER_PROP_RM => 'DeleteUserProperty',
        self::EVT_USER_PROP_ADD => 'AddUserProperty',
        self::EVT_PARTY_IMG_COMMENT => 'PartyImageGotComment',
        self::EVT_PARTY_IMG_LIKE => 'PartyImageGotLike',
        self::EVT_PARTY_VIDEO_LIKE => 'PartyVideoGotLike',
        self::EVT_PARTY_VIDEO_COMMENT => 'PartyVideoGotComment',
        self::EVT_CONTEST_COMMENT => 'ContestGotComment',
        self::EVT_POINTS_ADDED => 'PointsAdded',
        self::EVT_PARTY_NOTIFY => 'PartyNotify',
        self::EVT_CONTENT_LOOSE => 'ProjectUserContentLoose',
        self::EVT_POST_GOT_COMMENT_NOTIFICATION => 'PostGotCommentNotification',
        self::EVT_CODE_ACTIVATION_BY_PHONE => 'CodeActivationByPhone',
        self::EVT_USER_REGISTERED => 'UserRegistered',
        self::EVT_CODE_TRY => 'CodeTry',
        self::EVT_CODE_SUCCESS => 'CodeSuccess',
        self::EVT_CODE_ALREADY_REGISTERED => 'CodeAlreadyRegistered',
        self::EVT_CODE_NOT_EXISTS => 'CodeDoesNotExist',
        self::EVT_CODE_VALIDATION_FAILED => 'CodeRegistrationFailedValidation',
        self::EVT_POINTS_ADDED_MMP => 'PointsAddedMmp',
        self::EVT_PRIZE_ASSIGNED_MMP => 'PrizeAssignedMmp',
        self::EVT_PRIZE_ORDERED_MMP => 'PrizeOrderedMmp'
    );

    protected $pluginsClassMap = array(
        self::EVT_TAG_POST => 'EventProcessorTagAssignedToPostPlugin',
        self::EVT_POST_TAGS_RM => 'EventProcessorPostTagsRemovedPlugin',
        self::EVT_CONTENT_ACCEPTED => 'EventProcessorProjectUserContentPublishedPlugin',
        self::EVT_CONTENT_REJECTED => 'EventProcessorProjectUserContentRejectedPlugin',
        self::EVT_WINNER_ASSIGNED => 'EventProcessorWinnerAssignedPlugin',
        self::EVT_POST_REJECTED => 'EventProcessorPostRejectedPlugin',
        self::EVT_POST_GOT_LIKE => 'EventProcessorPostGotLikePlugin',
        self::EVT_POST_GOT_COMMENT => 'EventProcessorPostGotCommentPlugin',
        self::EVT_USER_VOTED => 'EventProcessorUserVotedInContestPlugin',
        self::EVT_USER_GOES_TO_EVT => 'EventProcessorUserGoesToPartyPlugin',
        self::EVT_FOLLOW => 'EventProcessorFollowPlugin',
        self::EVT_USER_PROP_RM => 'EventProcessorUserPropRemovedPlugin',
        self::EVT_USER_PROP_ADD => 'EventProcessorUserPropAddedPlugin',
        self::EVT_PARTY_IMG_COMMENT => 'EventProcessorPartyImageGotCommentPlugin',
        self::EVT_PARTY_IMG_LIKE => 'EventProcessorPartyImageGotLikePlugin',
        self::EVT_CONTEST_COMMENT => 'EventProcessorContestGotCommentPlugin',
        self::EVT_POINTS_ADDED => 'EventProcessorPointsAddedPlugin',
        self::EVT_PARTY_NOTIFY => 'EventProcessorPartyNotifyPlugin',
        self::EVT_CONTENT_LOOSE => 'EventProcessorProjectUserContentLoosePlugin',
        self::EVT_POST_GOT_COMMENT_NOTIFICATION => 'EventProcessorPostGotCommentNotificationPlugin',
        self::EVT_PARTY_VIDEO_COMMENT => 'EventProcessorPartyVideoGotCommentPlugin',
        self::EVT_PARTY_VIDEO_LIKE => 'EventProcessorPartyVideoGotLikePlugin',
        self::EVT_CODE_ACTIVATION_BY_PHONE => 'EventProcessorCodeActivationByPhonePlugin',
        self::EVT_USER_REGISTERED => 'EventProcessorUserRegisteredPlugin',
        self::EVT_CODE_TRY => 'EventProcessorCodeActivationTryPlugin',
        self::EVT_CODE_SUCCESS => 'EventProcessorCodeActivationSuccessPlugin',
        self::EVT_CODE_ALREADY_REGISTERED => 'EventProcessorCodeAlreadyRegisteredPlugin',
        self::EVT_CODE_NOT_EXISTS => 'EventProcessorCodeNotExistsPlugin',
        self::EVT_CODE_VALIDATION_FAILED => 'EventProcessorCodeValidationFailedPlugin',
        self::EVT_POINTS_ADDED_MMP => 'EventProcessorPointsAddedMmpPlugin',
        self::EVT_PRIZE_ASSIGNED_MMP => 'EventProcessorPrizeAssignedPlugin',
        self::EVT_PRIZE_ORDERED_MMP => 'EventProcessorPrizeOrderedPlugin',
    );

    public function processMessage(\ICRMRabbitManager $manager)
    {
        /* @var $envelope \AMQPEnvelope */
        $envelope = $manager->getEnvelope();
        $pluginsPath = __DIR__ . '/plugins/';
        foreach ($this->routingKeys as $key => $value) {
            if (strpos($envelope->getRoutingKey(), 'event.' . $key . '.') !== false) {
                if (!class_exists($this->pluginsClassMap[$key])) {
                    if (file_exists($pluginsPath . $this->pluginsClassMap[$key] . '.class.php')) {
                        require_once $pluginsPath . $this->pluginsClassMap[$key] . '.class.php';
                    }
                }
                if (class_exists($this->pluginsClassMap[$key])) {
                    try {
                        /* @var $class \IEventProcessorPlugin */
                        $class = new $this->pluginsClassMap[$key];
                        $class->setAppId($envelope->getAppId())
                            ->setRoutingKey($envelope->getRoutingKey())
                            ->setMessageId($envelope->getMessageId())
                            ->setBody($envelope->getBody())
                            ->setCorrelationId($envelope->getCorrelationId())
                            ->process($manager);
                        $manager->sendAck();
                    } catch (\Exception $e) {
                        $manager->sendNAck();
                        throw $e;
                    }
                } else {
                    $manager->sendNAck();
                    throw new Exception('Class ' . $this->pluginsClassMap[$key] . ' not found!');
                }
            }
        }
    }
}
