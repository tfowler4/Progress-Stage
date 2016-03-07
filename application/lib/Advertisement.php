<?php

/**
 * advertisement class to display specific ad content blocks
 */
class Advertisement {
    /**
     * @return string [ html string containing leaderboard advertisement code ]
     */
    public static function getLeaderboardAd() {
        $html = '';

        $html .= '<div class="text-center">';

         if ( !defined('WEBSERVER') || AD_HEADER != 1 ) {
            $html .= '<div style="display:inline-block;width:728px;height:90px; background-color:#000000;"></div>';
         } else {
            $html .= '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <!-- Leaderboard - General -->
                    <ins class="adsbygoogle"
                        style="display:inline-block;width:728px;height:90px"
                        data-ad-client="ca-pub-2757788921600999"
                        data-ad-slot="2593467268"></ins>
                    <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>';
         }

        $html .= '</div>';

        return $html;
    }

    /**
     * @return string [ html string containing sidebar medium advertisement code ]
     */
    public static function getSidebarMediumAd() {
        $html = '';

        $html .= '<div class="text-center">';
         if ( !defined('WEBSERVER') || AD_HEADER != 1 ) {
            $html .= '<div style="display:inline-block; width:300px;height:250px; background-color:#000000;"></div>';
         } else {
            $html = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <!-- Sidebar - Medium -->
                    <ins class="adsbygoogle"
                        style="display:inline-block;width:300px;height:250px"
                        data-ad-client="ca-pub-2757788921600999"
                        data-ad-slot="7978450468"></ins>
                    <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * @return string [ html string containing sidebar large advertisement code ]
     */
    public static function getSidebarLargeAd() {
        $html = '';

        $html .= '<div class="text-center">';

         if ( !defined('WEBSERVER') || AD_HEADER != 1 ) {
            $html .= '<div style="display:inline-block; width:300px;height:600px; background-color:#000000;"></div>';
         } else {
            $html .= '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <!-- Sidebar - Large -->
                    <ins class="adsbygoogle"
                        style="display:inline-block;width:300px;height:600px"
                        data-ad-client="ca-pub-2757788921600999"
                        data-ad-slot="6604864462"></ins>
                    <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>';
        }

        $html .= '</div>';

        return $html;
    }
}