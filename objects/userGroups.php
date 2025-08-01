<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';

class UserGroups{
    private $id;
    private $group_name;

    public function __construct($id, $group_name = "")
    {
        if (empty($id)) {
            $group_name = _substr($group_name, 0, 255);
            // get the category data from category and pass
            $this->group_name = $group_name;
        } else {
            // get data from id
            $this->load($id);
        }
    }

    public function load($id)
    {
        $user = self::getUserGroupsDb($id);
        if (empty($user)) {
            return false;
        }
        foreach ($user as $key => $value) {
            $this->$key = $value;
        }
    }

    private static function getUserGroupsDb($id)
    {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM users_groups WHERE  id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", [$id]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!empty($data)) {
            $user = $data;
        } else {
            $user = false;
        }
        return $user;
    }

    public function save()
    {
        global $global;
        if (empty($this->isAdmin)) {
            $this->isAdmin = "false";
        }
        $formats = '';
        $values = [];
        $this->group_name = _substr($this->group_name, 0, 255);
        if (!empty($this->id)) {
            $sql = "UPDATE users_groups SET group_name = ?, modified = now() WHERE id = ?";
            $formats = "si";
            $values = [$this->group_name,$this->id];
        } else {
            $sql = "INSERT INTO users_groups ( group_name, created, modified) VALUES (?,now(), now())";
            $formats = "s";
            $values = [$this->group_name];
        }
        if (sqlDAL::writeSql($sql, $formats, $values)) {
            if (empty($this->id)) {
                $id = $global['mysqli']->insert_id;
            } else {
                $id = $this->id;
            }
            return $id;
        } else {
            return false;
        }
    }

    public function delete()
    {
        if (!User::isAdmin()) {
            return false;
        }

        global $global;
        if (!empty($this->id)) {
            $sql = "DELETE FROM users_groups WHERE id = ?";
        } else {
            return false;
        }
        return sqlDAL::writeSql($sql, "i", [$this->id]);
    }

    private function getUserGroup($id)
    {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM users_groups WHERE  id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", [$id]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!empty($data)) {
            $category = $data;
        } else {
            $category = false;
        }
        return $category;
    }

    public static function getAllUsersGroups()
    {
        global $global;
        $sql = "SELECT *,"
                . " (SELECT COUNT(*) FROM videos_group_view WHERE users_groups_id = ug.id ) as total_videos, "
                . " (SELECT COUNT(*) FROM users_has_users_groups WHERE users_groups_id = ug.id ) as total_users "
                . " FROM users_groups as ug WHERE 1=1 ";

        $sql .= BootGrid::getSqlFromPost(['group_name']);

        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $arr = [];
        if ($res!=false) {
            foreach ($fullData as $row) {
                $arr[] = $row;
            }
            //$category = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $arr = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $arr;
    }

    public static function getAllUsersGroupsArray()
    {
        global $global;
        $sql = "SELECT * FROM users_groups as ug WHERE 1=1 ";

        $res = sqlDAL::readSql($sql);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $arr = [];
        if ($res!=false) {
            foreach ($fullData as $row) {
                $arr[$row['id']] = $row['group_name'];
            }
            //$category = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $arr = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $arr;
    }

    public static function getTotalUsersGroups()
    {
        global $global;
        $sql = "SELECT id FROM users_groups WHERE 1=1  ";

        $sql .= BootGrid::getSqlSearchFromPost(['group_name']);
        $res = sqlDAL::readSql($sql);
        $numRows = sqlDAL::num_rows($res);
        sqlDAL::close($res);
        return $numRows;
    }

    public function getGroup_name()
    {
        return $this->group_name;
    }

    public function setGroup_name($group_name)
    {
        $this->group_name = $group_name;
    }

    public static function getUserGroupByName($group_name, $refreshCache = false)
    {
        global $global;
        $sql = "SELECT * FROM users_groups WHERE  group_name = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "s", [$group_name], $refreshCache);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!empty($data)) {
            $category = $data;
        } else {
            $category = false;
        }
        return $category;
    }

    public static function getOrCreateUserGroups($group_name)
    {
        $group_name = trim($group_name);
        $group_name = _substr($group_name, 0, 255);
        if (empty($group_name)) {
            return false;
        }
        $group = self::getUserGroupByName($group_name, true);
        if (empty($group)) {
            $g = new UserGroups(0, $group_name);
            return $g->save();
        } else {
            return $group['id'];
        }
    }

    // for users

    public static function updateUserGroups($users_id, $array_groups_id, $byPassAdmin=false, $mergeWithCurrentUserGroups=false){
        if (!$byPassAdmin && !Permissions::canAdminUsers() && !isCommandLineInterface()) {
            return false;
        }
        if (!is_array($array_groups_id)) {
            return false;
        }
        if (empty($users_id)) {
            return false;
        }

        if ($mergeWithCurrentUserGroups) {
            $current_user_groups = self::getUserGroups($users_id);
            foreach ($current_user_groups as $value) {
                if (!in_array($value['id'], $array_groups_id)) {
                    $array_groups_id[] = $value['id'];
                }
            }
        }

        self::deleteGroupsFromUser($users_id, true);
        global $global;
        $array_groups_id = array_unique($array_groups_id);
        $sql = "INSERT INTO users_has_users_groups ( users_id, users_groups_id) VALUES (?,?)";
        foreach ($array_groups_id as $value) {
            $value = intval($value);
            if (empty($value)) {
                continue;
            }
            sqlDAL::writeSql($sql, "ii", [$users_id,$value]);
        }

        // make sure you do not save the dynamic user groups
        $user_groups_ids = AVideoPlugin::getDynamicUserGroupsId($users_id);
        if (!empty($user_groups_ids) && is_array($user_groups_ids)) {
            $sql = "DELETE FROM users_has_users_groups WHERE users_id = ? AND users_groups_id IN (". implode(',', $user_groups_ids).") ";
            return sqlDAL::writeSql($sql, "i", [$users_id]);
        }

        return true;
    }

    public static function getAlUserGroupsFromUser($users_id)
    {
        return self::getUserGroups($users_id);
    }

    public static function getUserGroups($users_id)
    {
        global $global;
        $res = sqlDAL::readSql("SHOW TABLES LIKE 'users_has_users_groups'");
        $result = sqlDAL::num_rows($res);
        sqlDAL::close($res);
        if (empty($result)) {
            $_GET['error'] = "You need to <a href='{$global['webSiteRootURL']}update'>update your system to ver 2.3</a>";
            return [];
        }
        if (empty($users_id)) {
            return [];
        }
        $sql = "SELECT uug.*, ug.* FROM users_groups ug"
                . " LEFT JOIN users_has_users_groups uug ON users_groups_id = ug.id WHERE users_id = ? ";

        $ids = AVideoPlugin::getDynamicUserGroupsId($users_id);
        if (!empty($ids) && is_array($ids)) {
            $ids = array_unique($ids);
            $sql .= " OR ug.id IN ('". implode("','", $ids)."') ";
        }
        //var_dump($ids);echo $sql;exit;
        $res = sqlDAL::readSql($sql, "i", [$users_id]);
        $fullData = sqlDal::fetchAllAssoc($res);
        sqlDAL::close($res);
        $arr = [];
        $doNotRepeat = [];
        if ($res!=false) {
            foreach ($fullData as $row) {
                if (in_array($row['id'], $doNotRepeat)) {
                    continue;
                }
                if (in_array($row['id'], $ids)) {
                    $row['isDynamic'] = 1;
                } else {
                    $row['isDynamic'] = 0;
                }
                $row = cleanUpRowFromDatabase($row);
                $doNotRepeat[] = $row['id'];
                $arr[] = $row;
            }
        } else {
            $arr = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $arr;
    }

    private static function deleteGroupsFromUser($users_id, $byPassAdmin=false)
    {
        if (!$byPassAdmin && !User::isAdmin()) {
            return false;
        }

        global $global;
        if (!empty($users_id)) {
            $sql = "DELETE FROM users_has_users_groups WHERE users_id = ?";
        } else {
            return false;
        }
        return sqlDAL::writeSql($sql, "i", [$users_id]);
    }

    public static function getVideoGroupsViewId($videos_id, $users_groups_id)
    {
        if (empty($videos_id)) {
            return false;
        }
        if (empty($users_groups_id)) {
            return false;
        }
        global $global;

        $sql = "SELECT id FROM videos_group_view WHERE videos_id = ? AND users_groups_id = ? LIMIT 1 ";
        $res = sqlDAL::readSql($sql, "ii", [$videos_id, $users_groups_id]);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if (!empty($data)) {
            return $data['id'];
        } else {
            return 0;
        }
    }

    public static function addVideoGroups($videos_id, $users_groups_id)
    {
        if (!User::canUpload()) {
            return false;
        }
        global $global;

        if (self::getVideoGroupsViewId($videos_id, $users_groups_id)) {
            return false;
        }

        $sql = "INSERT INTO videos_group_view ( videos_id, users_groups_id) VALUES (?,?)";
        $value = intval($value);
        $response = sqlDAL::writeSql($sql, "ii", [$videos_id,$users_groups_id]);

        if ($response) {
            Video::clearCache($videos_id);
        }
        return $response;
    }

    public static function deleteVideoGroups($videos_id, $users_groups_id)
    {
        if (!User::canUpload()) {
            return false;
        }

        $sql = "DELETE FROM videos_group_view WHERE videos_id = ? AND users_groups_id = ?";
        $response = sqlDAL::writeSql($sql, "ii", [$videos_id, $users_groups_id]);

        if ($response) {
            Video::clearCache($videos_id);
        }
        return $response;
    }

    public static function updateVideoGroups($videos_id, $array_groups_id, $mergeWithCurrentUserGroups=false)
    {
        if (empty($array_groups_id) || (!isCommandLineInterface() && !User::canUpload())) {
            return false;
        }
        if (!is_array($array_groups_id)) {
            $array_groups_id = array($array_groups_id);
        }

        if ($mergeWithCurrentUserGroups) {
            $current_user_groups = self::getVideosAndCategoriesUserGroups($videos_id);
            foreach ($current_user_groups as $value) {
                if (!in_array($value['id'], $array_groups_id)) {
                    if($value['isVideoUserGroup']){
                        $array_groups_id[] = $value['id'];
                    }
                }
            }
        }

        self::deleteGroupsFromVideo($videos_id);
        global $global;

        $sql = "INSERT INTO videos_group_view ( videos_id, users_groups_id) VALUES (?,?)";
        foreach ($array_groups_id as $value) {
            $value = intval($value);
            sqlDAL::writeSql($sql, "ii", [$videos_id,$value]);
        }

        return true;
    }

    public static function getVideoGroups($videos_id){
        if (empty($videos_id)) {
            return [];
        }
        global $global;
        $sql = "SELECT v.*, ug.*FROM videos_group_view as v "
                . " LEFT JOIN users_groups as ug ON users_groups_id = ug.id WHERE videos_id = ? ";
        $res = sqlDAL::readSql($sql, "i", [$videos_id]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $arr = [];
        if ($res!=false) {
            foreach ($fullData as $row) {
                $row = cleanUpRowFromDatabase($row);
                $arr[] = $row;
            }
        } else {
            $arr = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $arr;
    }
    
    public static function getCategoriesGroups($videos_id){
        if (empty($videos_id)) {
            return [];
        }
        global $global;
        
        $v = Video::getVideoLight($videos_id);
        
        $sql = "SELECT chug.*, ug.* FROM categories_has_users_groups as chug "
                . " LEFT JOIN users_groups as ug ON users_groups_id = ug.id WHERE categories_id = ? ";
        $res = sqlDAL::readSql($sql, "i", [$v['categories_id']]);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $arr = [];
        if ($res!=false) {
            foreach ($fullData as $row) {
                $row = cleanUpRowFromDatabase($row);
                $arr[] = $row;
            }
        } else {
            $arr = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $arr;
    }
    
    public static function getVideosAndCategoriesUserGroups($videos_id){
        global $_getVideosAndCategoriesUserGroups;
        
        if(!isset($_getVideosAndCategoriesUserGroups)){
            $_getVideosAndCategoriesUserGroups = array();
        }
        if(!isset($_getVideosAndCategoriesUserGroups[$videos_id])){
            $videosug = self::getVideoGroups($videos_id);
            $categoriessug = self::getCategoriesGroups($videos_id);
            $response = array();
            foreach ($videosug as $value) {
                $value['isVideoUserGroup'] = 1;
                $value['isCategoryUserGroup'] = 0;
                $response[$value['users_groups_id']] = $value;
            }
            foreach ($categoriessug as $value) {
                if(!isset($response[$value['users_groups_id']])){
                    $value['isVideoUserGroup'] = 0;
                    $value['isCategoryUserGroup'] = 1;
                    $response[$value['users_groups_id']] = $value;
                }else{
                    $response[$value['users_groups_id']]['isCategoryUserGroup'] = 1;
                }
            }
            $_getVideosAndCategoriesUserGroups[$videos_id] = $response;
        }
        return $_getVideosAndCategoriesUserGroups[$videos_id];
    }

    private static function deleteGroupsFromVideo($videos_id){
        if (!User::canUpload()) {
            return false;
        }

        global $global;
        if (!empty($videos_id)) {
            $sql = "DELETE FROM videos_group_view WHERE videos_id = ?";
        } else {
            return false;
        }
        return sqlDAL::writeSql($sql, "i", [$videos_id]);
    }
}
