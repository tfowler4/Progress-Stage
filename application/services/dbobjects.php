<?php

/**
 * database objects for insert/edlete/update queries
 */
class DBObjects {
    protected static $_queryObject;
    protected static $_sqlString;
    protected static $_successful;

    public static $insertId;

    /**
     * execute the sql query
     * 
     * @param string $id [ id of database column of lastInsertId ]
     * 
     * @return void
     */
    protected static function _execute($id = null) {
        $dbh = DbFactory::getDbh();
        self::$_queryObject = $dbh->prepare(self::$_sqlString);
        self::$_queryObject->execute();
        
        if ( $id) { self::$insertId = $dbh->lastInsertId($id); }
        Logger::log('INFO', 'Executed Query: ' . self::$_sqlString . ' - Insert ID: ' . self::$insertId);
    }

    /**
     * generate sql string to add a new guild to database
     * 
     * @param object $fields [ strings to display in log ]
     *
     * @return void
     */
    public static function addGuild($fields = null) {
        Logger::log('INFO', '***Preparing to add Guild: ' . $fields->guildName . '***');

        self::$_sqlString = sprintf(
            "INSERT INTO %s
            (name, faction, server, region, country, leader, website, facebook, twitter, google, creator_id)
            values('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
            DbFactory::TABLE_GUILDS,
            $fields->guildName,
            $fields->faction,
            $fields->server,
            $fields->region,
            $fields->country,
            $fields->guildLeader,
            $fields->website,
            $fields->facebook,
            $fields->twitter,
            $fields->google,
            $_SESSION['userId']
            );
        Logger::log('INFO', 'Preparing Query: ' . self::$_sqlString);
        self::_execute('guild_id');
    }

    /**
     * generate sql string to add a new raid team for guild to database
     *
     * @param object $fields        [ strings to display in log ]
     * @param string $userId        [ userId to be set as creator ]
     * @param string $parentDetails [ parent guild details object ]
     *
     * @return void
     */
    public static function addChildGuild($fields = null, $userId, $parentDetails) {
        Logger::log('INFO', '***Preparing to add Raid Team: ' . $fields->guildName . '***');

        self::$_sqlString = sprintf(
            "INSERT INTO %s
            (name, faction, server, region, country, leader, website, facebook, twitter, google, creator_id, parent, type)
            values('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
            DbFactory::TABLE_GUILDS,
            $fields->guildName,
            $fields->faction,
            $fields->server,
            $fields->region,
            $fields->country,
            $fields->guildLeader,
            $parentDetails->_website,
            $parentDetails->_facebook,
            $parentDetails->_twitter,
            $parentDetails->_google,
            $userId,
            $parentDetails->_guildId,
            1
            );
        Logger::log('INFO', 'Preparing Query: ' . self::$_sqlString);
        self::_execute('guild_id');

        $childrenGuilds = $parentDetails->_child;

        if ( empty($childrenGuilds) ) {
            $childrenGuilds = self::$insertId;
        } else {
            $childrenGuilds .= '||' . self::$insertId;
        }

        self::$_sqlString = sprintf(
            "UPDATE %s
                SET child = '%s'
              WHERE guild_id = '%s'",
            DbFactory::TABLE_GUILDS,
            $childrenGuilds,
            $parentDetails->_guildId
            );
        Logger::log('INFO', 'Preparing Query: ' . self::$_sqlString);
        self::_execute();
    }


    /**
     * generate sql string to edit guild in database
     *
     * @param object $fields       [ strings to display in log ]
     * @param string $guildDetails [ guild details object ]
     *
     * @return void
     */
    public static function editGuild($fields = null, $guildDetails) {
        Logger::log('INFO', '***Preparing to edit Guild: ' . $guildDetails->_name . '***');

        self::$_sqlString = sprintf(
            "UPDATE %s
                SET faction = '%s',
                    server = '%s',
                    region = '%s', 
                    country = '%s',
                    leader = '%s',
                    website = '%s',
                    facebook = '%s',
                    twitter = '%s',
                    google = '%s'
              WHERE guild_id = '%s'",
            DbFactory::TABLE_GUILDS,
            $fields->faction,
            $fields->server,
            $fields->region,
            $fields->country,
            $fields->guildLeader,
            $fields->website,
            $fields->facebook,
            $fields->twitter,
            $fields->google,
            $guildDetails->_guildId
            );
        Logger::log('INFO', 'Preparing Query: ' . self::$_sqlString);
        self::_execute();
    }

    /**
     * generate sql string to remove a guild from database
     * 
     * @param object $fields [ strings to display in log ]
     *
     * @return void
     */
    public static function removeGuild($fields = null) {
        Logger::log('INFO', '***Preparing to remove Guild: ' . $fields->guildId . '***');

        self::$_sqlString = sprintf(
            "DELETE
               FROM %s
              WHERE guild_id='%s'",
             DbFactory::TABLE_GUILDS,
             $fields->guildId
            );
        Logger::log('INFO', 'Preparing Query: ' . self::$_sqlString);
        self::_execute();
    }

    /**
     * generate sql string to remove a raid team of a guild from database
     * 
     * @param string $childId  [ raid team id ]
     * @param string $parentId [ parent guild id ]
     *
     * @return void
     */
    public static function removeChildGuild($childId, $parentId) {
        self::$_sqlString = sprintf(
            "UPDATE %s
                SET child = '%s'
              WHERE guild_id = '%s'",
            DbFactory::TABLE_GUILDS,
            $childId,
            $parentId
            );
        Logger::log('INFO', 'Preparing Query: ' . self::$_sqlString);
        self::_execute();
    }

    /**
     * generate sql string to add a kill to a guild from database
     * 
     * @param object $fields [ strings to display in log ]
     * @param string $sql    [ progression column string ]
     *
     * @return void
     */
    public static function addKill($fields = null, $sql) {
        Logger::log('INFO', '***Preparing to add Kill: ' . $fields->encounter . ' for Guild: ' . $fields->guildId . '***');

        self::$_sqlString = sprintf(
            "UPDATE %s
                SET progression ='%s'
              WHERE guild_id='%s'",
             DbFactory::TABLE_GUILDS,
             $sql,
             $fields->guildId
             );
        Logger::log('INFO', 'Preparing Query: ' . self::$_sqlString);
        self::_execute();

        $date      = $fields->dateYear . '-' . $fields->dateMonth . '-' . $fields->dateDay;
        $time      = $fields->dateHour . ':' . $fields->dateMinute;
        $strtotime = strtotime($date . ' ' . $time);

        self::$_sqlString = sprintf(
            "INSERT INTO %s
            (guild_id, encounter_id, strtotime)
            values('%s','%s','%s')",
             DbFactory::TABLE_RECENT_RAIDS,
             $fields->guildId,
             $fields->encounter,
             $strtotime
            );
        Logger::log('INFO', 'Preparing Query: ' . self::$_sqlString);
        self::_execute('guild_id');
    }

    /**
     * generate sql string to edit a kill to a guild from database
     * 
     * @param object $fields [ strings to display in log ]
     * @param string $sql    [ progression column string ]
     *
     * @return void
     */
    public static function editKill($fields = null, $sql) {
        Logger::log('INFO', '***Preparing to edit Kill: ' . $fields->encounter . ' for Guild: ' . $fields->guildId . '***');

        self::$_sqlString = sprintf(
            "UPDATE %s
                SET progression ='%s'
              WHERE guild_id='%s'",
             DbFactory::TABLE_GUILDS,
             $sql,
             $fields->guildId
             );
        Logger::log('INFO', 'Preparing Query: ' . self::$_sqlString);
        self::_execute();

        $date      = $fields->dateYear . '-' . $fields->dateMonth . '-' . $fields->dateDay;
        $time      = $fields->dateHour . ':' . $fields->dateMinute;
        $strtotime = strtotime($date . ' ' . $time);

        self::$_sqlString = sprintf(
            "UPDATE %s
                SET strtotime='%s'
              WHERE guild_id='%s'
                AND encounter_id='%s'",
             DbFactory::TABLE_RECENT_RAIDS,
             $strtotime,
             $fields->guildId,
             $fields->encounter
            );
        Logger::log('INFO', 'Preparing Query: ' . self::$_sqlString);
        self::_execute();
    }

    /**
     * generate sql string to remove a kill from a guild from database
     * 
     * @param object $fields [ strings to display in log ]
     * @param string $sql    [ progression column string ]
     *
     * @return void
     */
    public static function removeKill($fields = null, $sql) {
        Logger::log('INFO', '***Preparing to remove Kill: ' . $fields->encounter . ' for Guild: ' . $fields->guildId . '***');

        self::$_sqlString = sprintf(
            "UPDATE %s
                SET progression ='%s'
              WHERE guild_id='%s'",
             DbFactory::TABLE_GUILDS,
             $sql,
             $fields->guildId
             );
        Logger::log('INFO', 'Preparing Query: ' . self::$_sqlString);
        self::_execute();
    }

    /**
     * generate sql string to add a new user to database
     * 
     * @param object $fields [ strings to display in log ]
     *
     * @return void
     */
    public static function addUser($fields = null) {
        Logger::log('INFO', '***Preparing to add User: ' . $fields->username . '***');

        self::$_sqlString = sprintf(
            "INSERT INTO %s
            (username, email, passcode, active)
            values('%s','%s','%s','%s')",
            DbFactory::TABLE_USERS,
            $fields->username,
            $fields->email,
            $fields->password,
            1
            );
        Logger::log('INFO', 'Preparing Query: ' . self::$_sqlString);
        self::_execute('user_id');
    }

    /**
     * generate sql string to edit user's email in database
     * 
     * @param object $fields [ strings to display in log ]
     *
     * @return void
     */
    public static function editUserEmail($fields = null) {
        Logger::log('INFO', '***Preparing to edit User Email: ' . $fields->userId . '***');

        self::$_sqlString = sprintf(
            "UPDATE %s
                SET email ='%s'
              WHERE user_id='%s'",
             DbFactory::TABLE_USERS,
             $fields->email,
             $fields->userId
             );
        Logger::log('INFO', 'Preparing Query: ' . self::$_sqlString);
        self::_execute();
    }

    /**
     * generate sql string to edit user's password in database
     * 
     * @param object $fields [ strings to display in log ]
     *
     * @return void
     */
    public static function editUserPassword($fields = null) {
        Logger::log('INFO', '***Preparing to edit User Password: ' . $fields->userId . '***');

        self::$_sqlString = sprintf(
            "UPDATE %s
                SET passcode ='%s'
              WHERE user_id='%s'",
             DbFactory::TABLE_USERS,
             $fields->newPassword,
             $fields->userId
             );
        Logger::log('INFO', 'Preparing Query: ' . self::$_sqlString);
        self::_execute();
    }
}