<?php

include 'script.php';

class Twitch extends Script {
    protected static $_streamData;
    protected static $_twitchChannels = array();
    protected static $_activeChannels = array();

    const IMAGE_HEIGHT = 500;
    const IMAGE_WIDTH  = 900;

    public static function init() {
        Logger::log('INFO', 'Starting Update Twitch Channel...');

        self::cleanTwitchDirectory();
        self::getTwitchChannels();

        $directory = strtolower(ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/twitch/');
        if( !is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        foreach ( self::$_twitchChannels as $twitchId => $twitchDetails) {
            $twitchObject = self::getChannelStream($twitchDetails['twitch_id']);

            if ( isset($twitchObject['stream']['_id']) && isset($twitchObject['stream']['preview']['large']) ) {
                if ( !empty($twitchObject['stream']['game']) && $twitchObject['stream']['game'] != GAME_NAME_1 ) { continue; }

                // Resize the image to what we want on the news page
                $twitchImage = $twitchObject['stream']['preview']['template'];
                $twitchImage = str_replace('{width}', self::IMAGE_WIDTH, $twitchImage);
                $twitchImage = str_replace('{height}', self::IMAGE_HEIGHT, $twitchImage);

                $twitchUrl = 'http://www.twitch.tv/' . $twitchDetails['twitch_id'];

                //Move file to folder
                echo "File Moving Begin<br>";

                $imagePath = $directory . $twitchId;

                if ( file_exists($imagePath) ) {
                    unlink($imagePath);
                }

                file_put_contents($imagePath, file_get_contents($twitchImage));
                echo "File Moving Done<br>";

                self::$_activeChannels[$twitchDetails['twitch_id']] = $twitchDetails['twitch_id'];
            } 
        }

        Logger::log('INFO', 'Setting Active Channels...');

        self::setActiveChannels();

        Logger::log('INFO', 'Update Twitch Channel Complete!');
    }

    public static function cleanTwitchDirectory() {
        $path = strtolower(ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/twitch/');

        $files = glob($path . '*'); // get all file names

        foreach($files as $file){ // iterate files
          if(is_file($file))
            unlink($file); // delete file
        }
    }

    public static function getTwitchChannels() {
        $query = self::$_dbh->query(sprintf(
            "SELECT *
               FROM twitch_table"
            ));
        $query->execute();

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { self::$_twitchChannels[$row['twitch_id']] = $row; }
    }

    public static function setActiveChannels() {
        $sql = '';

        foreach ( self::$_activeChannels as $twitchId ) {
            if ( empty($sql) ) {
                $sql = "twitch_id ='" . $twitchId . "'";
            } else {
                $sql .= " OR twitch_id ='" .$twitchId . "'";
            }
        }

        $query = self::$_dbh->query(sprintf(
            "UPDATE twitch_table
                SET active = '0'"
            ));
        $query->execute();

        if ( !empty($sql) ) {
            $query = self::$_dbh->query(sprintf(
                "UPDATE twitch_table
                    SET active = '%s'
                  WHERE %s",
                1,
                $sql
                ));
            $query->execute();
        }
    }

    public static function getApiUri($type) {
        $apiUrl = "https://api.twitch.tv/kraken";
        $apiCalls = array(
            "streams" => $apiUrl."/streams/",
            "search" => $apiUrl."/search/",
            "channel" => $apiUrl."/channels/",
            "user" => $apiUrl."/user/",
            "teams" => $apiUrl."/teams/",
        );
        return $apiCalls[$type];
    }

    public static function getFeatured($game) {
        $s = file_get_contents(self::getApiUri("streams")."?game=".urlencode($game)."&limit=1&offset=0");
        $activeStreams = json_decode($s, true);
        $streams = $activeStreams["streams"];
        foreach($streams as $stream) {
            return $stream["channel"]["name"];
        }
    }

    public static function getStreams($game, $page, $limit) {
        $offset = ($page-1)*$limit;
        $s = file_get_contents(self::getApiUri("streams")."?game=".urlencode($game)."&limit=$limit&offset=$offset");
        $activeStreams = json_decode($s, true);
        $streams = $activeStreams["streams"];
        $final = "";
        foreach($streams as $stream) {
            $imgsm = $stream["preview"]["small"];
            $imgmed = $stream["preview"]["medium"];
            $viewers = $stream["viewers"];
            $channel = $stream["channel"];
            $status = $channel["status"];
            $twitchName = $channel["name"];
            $twitchDisplay = $channel["display_name"];
            $twitchLink = $channel["url"];
            $final .= "<a class=\"stream-item\" href=\"/users/$twitchName\"><img src=\"$imgmed\"><span class=\"name\">$status</span><span class=\"viewers\">$viewers viewers</span></a>";
        }
        echo $final;
    }

    public static function getChannel($channel) {
        $c = file_get_contents(self::getApiUri("channel").$channel);
        $channelData = json_decode($c, true);
        return $channelData;
    }

    public static function getFollowers($channel) {
        $f = file_get_contents(self::getApiUri("channel").$channel."/follows");
        $followData = json_decode($f, true);
        return $followData["_total"];
    }

    public static function getChannelStream($channel) {
        $s = file_get_contents(self::getApiUri("streams").$channel);
        $streamData = json_decode($s, true);
        return $streamData;
    }
}

Twitch::init();