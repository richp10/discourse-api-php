<?php

    /**
     * Discourse API client for PHP
     *
     * Expanded on original by DiscourseHosting
     *
     * @category     DiscourseAPI
     * @package      DiscourseAPI
     * @author       Original author DiscourseHosting <richard@discoursehosting.com>
     * Additional work, timolaine, richp10 and others..
     * @copyright    2013, DiscourseHosting.com
     * @license      http://www.gnu.org/licenses/gpl-2.0.html GPLv2
     * @link         https://github.com/richp10/discourse-api-php
     *
     * @noinspection MoreThanThreeArgumentsInspection
     **/

    namespace richp10\discourseAPI;

    class DiscourseAPI
    {
        private $_protocol;
        private $_apiKey;
        private $_dcHostname;

        ////////////////  Groups

        /**
         * getGroups
         *
         * @return mixed HTTP return code and API return object
         */
        public function getGroups()
        {
            return $this->_getRequest('/groups.json');
        }
        
        /**
         * getGroup
         *
         * @param string $group name of group
         * @return mixed HTTP return code and API return object
         */

        public function getGroup($groupname)
        {
            return $this->_getRequest('/groups/' . $groupname . '.json');
        }

        /**
         * joinGroup
         *
         * @param string $groupname name of group
         * @param string $username  user to add to the group
         *
         * @return mixed HTTP return code and API return object
         */
        public function joinGroup($groupname, $username)
        {
            $groupId = $this->getGroupIdByGroupName($groupname);
            if (!$groupId) {
                return false;
            }

            $params = [
                'usernames' => $username
            ];

            return $this->_putRequest('/groups/' . $groupId . '/members.json', [$params]);
        }

        /*
         * getGroupIdByGroupName
         *
         * @param string $groupname    name of group
         *
         * @return mixed id of the group, or false if nonexistent
         */
        public function getGroupIdByGroupName($groupname)
        {
            $obj = $this->getGroup($groupname);
            if ($obj->http_code !== 200) {
                return false;
            }

            return $obj->apiresult->group->id;
        }

        /**
         * @param $groupname
         * @param $username
         * @return bool|\stdClass
         */
        public function leaveGroup($groupname, $username)
        {
            $userid  = $this->getUserByUsername($username)->apiresult->user->id;
            $groupId = $this->getGroupIdByGroupName($groupname);
            if (!$groupId) {
                return false;
            }
            $params = [
                'user_id' => $userid
            ];

            return $this->_deleteRequest('/groups/' . $groupId . '/members.json', [$params]);
        }

        /**
         * getGroupMembers
         *
         * @param string $group name of group
         * @return mixed HTTP return code and API return object
         */
        public function getGroupMembers($group)
        {
            return $this->_getRequest("/groups/{$group}/members.json");
        }

        /** @noinspection MoreThanThreeArgumentsInspection */
        /**
         * group
         *
         * @param string $groupname name of group to be created
         * @param array  $usernames users in the group
         *
         * @param int    $aliaslevel
         * @param string $visible
         * @param string $automemdomain
         * @param string $automemretro
         * @param string $title
         * @param string $primegroup
         * @param string $trustlevel
         * @return mixed HTTP return code and API return object
         * @noinspection MoreThanThreeArgumentsInspection
         **/
        public function addGroup(
            $groupname,
            array $usernames = [],
            $aliaslevel = 3,
            $visible = 'true',
            $automemdomain = '',
            $automemretro = 'false',
            $title = '',
            $primegroup = 'false',
            $trustlevel = '0'
        ) {
            $groupId = $this->getGroupIdByGroupName($groupname);
            if ($groupId) {
                return false;
            }

            $params = [
                'group' => [
                    'name'                               => $groupname,
                    'usernames'                          => implode(',', $usernames),
                    'alias_level'                        => $aliaslevel,
                    'visible'                            => $visible,
                    'automatic_membership_email_domains' => $automemdomain,
                    'automatic_membership_retroactive'   => $automemretro,
                    'title'                              => $title,
                    'primary_group'                      => $primegroup,
                    'grant_trust_level'                  => $trustlevel
                ]
            ];

            return $this->_postRequest('/admin/groups', $params);
        }

        /**
         * @param string $groupname
         * @return bool|\stdClass
         */
        public function removeGroup(string $groupname)
        {
            $groupId = $this->getGroupIdByGroupName($groupname);
            if (!$groupId) {
                return false;
            }

            return $this->_deleteRequest('/admin/groups/' . (string)$groupId, []);
        }

        ///////////////   Categories

        /** @noinspection MoreThanThreeArgumentsInspection * */
        /**
         * createCategory
         *
         * @param string $categoryName name of new category
         * @param string $color        color code of new category (six hex chars, no #)
         * @param string $textColor    optional color code of text for new category
         * @param string $userName     optional user to create category as
         *
         * @return mixed HTTP return code and API return object
         **/
        public function createCategory(string $categoryName, string $color, string $textColor = '000000', string $userName = 'system')
        {
            $params = [
                'name'       => $categoryName,
                'color'      => $color,
                'text_color' => $textColor
            ];

            return $this->_postRequest('/categories', [$params], $userName);
        }

        /**
         * @param $categoryName
         * @return \stdClass
         */
        public function getCategory($categoryName): \stdClass
        {
            return $this->_getRequest("/c/{$categoryName}.json");
        }

        /** @noinspection MoreThanThreeArgumentsInspection * */
        /**
         * Edit Category
         *
         * @param integer    $catid
         * @param string     $allow_badges
         * @param string     $auto_close_based_on_last_post
         * @param string     $auto_close_hours
         * @param string     $background_url
         * @param string     $color
         * @param string     $contains_messages
         * @param string     $email_in
         * @param string     $email_in_allow_strangers
         * @param string     $logo_url
         * @param string     $name
         * @param int|string $parent_category_id
         * @param            $groupname
         * @param int|string $position
         * @param string     $slug
         * @param string     $suppress_from_homepage
         * @param string     $text_color
         * @param string     $topic_template
         * @param array      $permissions
         * @return mixed HTTP return code and API return object
         */
        public function updatecat(
            $catid,
            $allow_badges = 'true',
            $auto_close_based_on_last_post = 'false',
            $auto_close_hours = '',
            $background_url,
            $color = '0E76BD',
            $contains_messages = 'false',
            $email_in = '',
            $email_in_allow_strangers = 'false',
            $logo_url = '',
            $name = '',
            $parent_category_id = '',
            $groupname,
            $position = '',
            $slug = '',
            $suppress_from_homepage = 'false',
            $text_color = 'FFFFFF',
            $topic_template = '',
            $permissions
        ) {
            $params = [
                'allow_badges'                  => $allow_badges,
                'auto_close_based_on_last_post' => $auto_close_based_on_last_post,
                'auto_close_hours'              => $auto_close_hours,
                'background_url'                => $background_url,
                'color'                         => $color,
                'contains_messages'             => $contains_messages,
                'email_in'                      => $email_in,
                'email_in_allow_strangers'      => $email_in_allow_strangers,
                'logo_url'                      => $logo_url,
                'name'                          => $name,
                'parent_category_id'            => $parent_category_id,
                'position'                      => $position,
                'slug'                          => $slug,
                'suppress_from_homepage'        => $suppress_from_homepage,
                'text_color'                    => $text_color,
                'topic_template'                => $topic_template
            ];

            # Add the permissions - this is an array of group names and integer permission values.
            if (count($permissions) > 0) {
                foreach ($permissions as $key => $value) {
                    $params['permissions[' . $key . ']'] = $permissions[$key];
                }
            }

            # This must PUT
            return $this->_putRequest('/categories/' . $catid, [$params]);
        }

        /**
         * getCategories
         *
         * @return mixed HTTP return code and API return object
         */
        public function getCategories()
        {
            return $this->_getRequest('/categories.json');
        }

        //////////////   USERS

        /**
         * createUser
         *
         * @param string $userName     username of new user
         *
         * @return mixed HTTP return code and API return object
         */
        public function logoutUser(string $userName)
        {
            $userid  = $this->getUserByUsername($userName)->apiresult->user->id;
            if (!\is_int($userid)) {
                return false;
            }

            return $this->_postRequest('/admin/users/'.$userid.'/log_out', []);
        }


        /** @noinspection MoreThanThreeArgumentsInspection */
        /**
         * createUser
         *
         * @param string $name         name of new user
         * @param string $userName     username of new user
         * @param string $emailAddress email address of new user
         * @param string $password     password of new user
         *
         * @return mixed HTTP return code and API return object
         */
        public function createUser(string $name, string $userName, string $emailAddress, string $password)
        {
            $obj = $this->_getRequest('/users/hp.json');
            if ($obj->http_code !== 200) {
                return false;
            }

            $params = [
                'name'                  => $name,
                'username'              => $userName,
                'email'                 => $emailAddress,
                'password'              => $password,
                'challenge'             => strrev($obj->apiresult->challenge),
                'password_confirmation' => $obj->apiresult->value
            ];

            return $this->_postRequest('/users', [$params]);
        }

        /**
         * activateUser
         *
         * @param integer $userId id of user to activate
         *
         * @return mixed HTTP return code
         */
        public function activateUser($userId)
        {
            return $this->_putRequest("/admin/users/{$userId}/activate", []);
        }

        /**
         * getUsernameByEmail
         *
         * @param string $email email of user
         *
         * @return mixed HTTP return code and API return object
         */
        public function getUsernameByEmail($email)
        {
            $users = $this->_getRequest('/admin/users/list/active.json?filter=' . urlencode($email));
            foreach ($users->apiresult as $user) {
                if ($user->email === $email) {
                    return $user->username;
                }
            }

            return false;
        }

        /**
         * getUserByUsername
         *
         * @param string $userName username of user
         *
         * @return mixed HTTP return code and API return object
         */
        public function getUserByUsername($userName)
        {
            return $this->_getRequest("/users/{$userName}.json");
        }

        /**
         * getUserByExternalID
         *
         * @param string $externalID     external id of sso user
         *
         * @return mixed HTTP return code and API return object
         */
        function getUserByExternalID($externalID)
        {
            return $this->_getRequest("/users/by-external/{$externalID}.json");
        }

        /**
         * @param        $email
         * @param        $topicId
         * @param string $userName
         * @return \stdClass
         */
        public function inviteUser($email, $topicId, $userName = 'system'): \stdClass
        {
            $params = [
                'email'    => $email,
                'topic_id' => $topicId
            ];

            return $this->_postRequest('/t/' . (int)$topicId . '/invite.json', [$params], $userName);
        }

        /**
         * getUserByEmail
         *
         * @param string $email email of user
         *
         * @return mixed user object
         */
        public function getUserByEmail($email)
        {
            $users = $this->_getRequest('/admin/users/list/active.json', [
                'filter' => $email
            ]);
            foreach ($users->apiresult as $user) {
                if (strtolower($user->email) === strtolower($email)) {
                    return $user;
                }
            }

            return false;
        }

        /**
         * getUserBadgesByUsername
         *
         * @param string $userName username of user
         *
         * @return mixed HTTP return code and list of badges for given user
         */
        public function getUserBadgesByUsername($userName)
        {
            return $this->_getRequest("/user-badges/{$userName}.json");
        }

        ///////////////  POSTS

        /**
         * createPost
         *
         * NOT WORKING YET
         *
         * @param $bodyText
         * @param $topicId
         * @param $userName
         * @return \stdClass
         */
        public function createPost(string $bodyText, $topicId, string $userName): \stdClass
        {
            $params = [
                'raw'       => $bodyText,
                'archetype' => 'regular',
                'topic_id'  => $topicId
            ];

            return $this->_postRequest('/posts', [$params], $userName);
        }

        /**
         * getPostsByNumber
         *
         * @param $topic_id
         * @param $post_number
         * @return mixed HTTP return code and API return object
         */
        public function getPostsByNumber($topic_id, $post_number)
        {
            return $this->_getRequest('/posts/by_number/' . $topic_id . '/' . $post_number . '.json');
        }

        /**
         * UpdatePost
         *
         * @param        $bodyhtml
         * @param        $post_id
         * @param string $userName
         * @return \stdClass
         */
        public function updatePost($bodyhtml, $post_id, $userName = 'system'): \stdClass
        {
            $bodyraw = htmlspecialchars_decode($bodyhtml);
            $params  = [
                'post[cooked]'      => $bodyhtml,
                'post[edit_reason]' => '',
                'post[raw]'         => $bodyraw
            ];

            return $this->_putRequest('/posts/' . $post_id, [$params], $userName);
        }

        //////////////  TOPICS

        /** @noinspection MoreThanThreeArgumentsInspection * */
        /**
         * createTopic
         *
         * @param string $topicTitle title of topic
         * @param string $bodyText   body text of topic post
         * @param string $categoryId
         * @param string $userName   user to create topic as
         * @param int    $replyToId  post id to reply as
         * @return mixed HTTP return code and API return object
         * @internal param string $categoryName category to create topic in
         **/
        public function createTopic(string $topicTitle, string $bodyText, string $categoryId, string $userName, int $replyToId = 0)
        {
            $params = [
                'title'                => $topicTitle,
                'raw'                  => $bodyText,
                'category'             => $categoryId,
                'archetype'            => 'regular',
                'reply_to_post_number' => $replyToId
            ];

            return $this->_postRequest('/posts', [$params], $userName);
        }

        /**
         * getTopic
         *
         * @param $topicId
         * @return \stdClass
         */
        public function getTopic($topicId): \stdClass
        {
            return $this->_getRequest("/t/{$topicId}.json");
        }

        /**
         * topTopics
         *
         * @param string $category slug of category
         * @param string $period   daily, weekly, monthly, yearly
         *
         * @return mixed HTTP return code and API return object
         */
        public function topTopics($category, $period = 'daily')
        {
            return $this->_getRequest('/c/' . $category . '/l/top/' . $period . '.json');
        }

        /**
         * latestTopics
         *
         * @param string $category slug of category
         *
         * @return mixed HTTP return code and API return object
         */
        public function latestTopics($category)
        {
            return $this->_getRequest('/c/' . $category . '/l/latest.json');
        }

        ////////////// MISC

        /**
         * @param $siteSetting
         * @param $value
         * @return \stdClass
         */
        public function changeSiteSetting($siteSetting, $value): \stdClass
        {
            $params = [
                $siteSetting => $value
            ];

            return $this->_putRequest('/admin/site_settings/' . $siteSetting, [$params]);
        }


        //////////////// Private Functions

        /** @noinspection MoreThanThreeArgumentsInspection */
        /**
         * @param string $reqString
         * @param array  $paramArray
         * @param string $apiUser
         * @param string $HTTPMETHOD
         * @return \stdClass
         *
         **/
        private function _getRequest(string $reqString, array $paramArray = [], string $apiUser = 'system', $HTTPMETHOD = 'GET'): \stdClass
        {
            $paramArray['api_key']      = $this->_apiKey;
            $paramArray['api_username'] = $apiUser;
            $paramArray['show_emails']  = 'true';
            $ch                         = curl_init();
            $url                        = sprintf('%s://%s%s?%s', $this->_protocol, $this->_dcHostname, $reqString, http_build_query($paramArray));
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $HTTPMETHOD);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $body = curl_exec($ch);
            $rc   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $resObj            = new \stdClass();
            $resObj->http_code = $rc;
            // Only return valid json
            $json              = json_decode($body);
            $resObj->apiresult = $body;
            if (json_last_error() === JSON_ERROR_NONE) {
                $resObj->apiresult = $json;
            }

            return $resObj;
        }

        /** @noinspection MoreThanThreeArgumentsInspection * */
        /**
         * @param string $reqString
         * @param array  $paramArray
         * @param string $apiUser
         * @param string $HTTPMETHOD
         * @return \stdClass
         **/
        private function _putpostRequest(string $reqString, array $paramArray, string $apiUser = 'system', $HTTPMETHOD = 'POST'): \stdClass
        {
            $ch  = curl_init();
            $url = sprintf('%s://%s%s?api_key=%s&api_username=%s', $this->_protocol, $this->_dcHostname, $reqString, $this->_apiKey, $apiUser);
            curl_setopt($ch, CURLOPT_URL, $url);
            $query = '';
            if (isset($paramArray['group']) && is_array($paramArray['group'])) {
                $query = http_build_query($paramArray);
            } else {
                if (is_array($paramArray[0])) {
                    foreach ($paramArray[0] as $param => $value) {
                        $query .= $param . '=' . urlencode($value) . '&';
                    }
                }
            }
            $query = trim($query, '&');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $HTTPMETHOD);
            $body = curl_exec($ch);
            $rc   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $resObj            = new \stdClass();
            $json              = json_decode($body);
            $resObj->apiresult = $body;
            if (json_last_error() === JSON_ERROR_NONE) {
                $resObj->apiresult = $json;
            }

            $resObj->http_code = $rc;

            return $resObj;
        }

        /**
         * @param string $reqString
         * @param array  $paramArray
         * @param string $apiUser
         * @return \stdClass
         */
        private function _deleteRequest(string $reqString, array $paramArray, string $apiUser = 'system'): \stdClass
        {
            return $this->_putpostRequest($reqString, $paramArray, $apiUser, 'DELETE');
        }

        /**
         * @param string $reqString
         * @param array  $paramArray
         * @param string $apiUser
         * @return \stdClass
         */
        private function _putRequest(string $reqString, array $paramArray, string $apiUser = 'system'): \stdClass
        {
            return $this->_putpostRequest($reqString, $paramArray, $apiUser, 'PUT');
        }

        /**
         * @param string $reqString
         * @param array  $paramArray
         * @param string $apiUser
         * @return \stdClass
         */
        private function _postRequest(string $reqString, array $paramArray, string $apiUser = 'system'): \stdClass
        {
            /** @noinspection ArgumentEqualsDefaultValueInspection * */
            return $this->_putpostRequest($reqString, $paramArray, $apiUser, 'POST');
        }

        /**
         * DiscourseAPI constructor.
         *
         * @param        $dcHostname
         * @param null   $apiKey
         * @param string $protocol
         */
        public function __construct($dcHostname, $apiKey = null, $protocol = 'http')
        {
            $this->_dcHostname = $dcHostname;
            $this->_apiKey     = $apiKey;
            $this->_protocol   = $protocol;
        }

    }
