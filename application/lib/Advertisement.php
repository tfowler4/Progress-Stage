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

        if ( !defined('WEBSERVER') || AD_HEADER != 1 ) { return $html; }

        $html .= '<div class="advertisement-leaderboard">';
        $html .= '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                <!-- Leaderboard - General -->
                <ins class="adsbygoogle"
                    style="display:inline-block;width:728px;height:90px"
                    data-ad-client="ca-pub-2757788921600999"
                    data-ad-slot="2593467268"></ins>
                <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
                </script>';
        $html .= '</div>';
        $html .= '<div class="vertical-separator"></div>';

        return $html;
    }

    /**
     * @return string [ html string containing sidebar medium advertisement code ]
     */
    public static function getSidebarMediumAd() {
        $html = '';

         if ( !defined('WEBSERVER') || AD_SIDEBAR != 1 ) { return $html; }

        $html .= '<div class="vertical-separator"></div>';
        $html .= '<div class="advertisement-sidebar-medium">';
        $html = '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                <!-- Sidebar - Medium -->
                <ins class="adsbygoogle"
                    style="display:inline-block;width:300px;height:250px"
                    data-ad-client="ca-pub-2757788921600999"
                    data-ad-slot="7978450468"></ins>
                <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
                </script>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @return string [ html string containing sidebar large advertisement code ]
     */
    public static function getSidebarLargeAd() {
        $html = '';

        if ( !defined('WEBSERVER') || AD_SIDEBAR != 1 ) { return $html; }

        $html .= '<div class="vertical-separator"></div>';
        $html .= '<div class="advertisement-sidebar-large">';
        $html .= '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                <!-- Sidebar - Large -->
                <ins class="adsbygoogle"
                    style="display:inline-block;width:300px;height:600px"
                    data-ad-client="ca-pub-2757788921600999"
                    data-ad-slot="6604864462"></ins>
                <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
                </script>';
        $html .= '</div>';

        return $html;
    }
}