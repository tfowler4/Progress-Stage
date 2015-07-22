<?php
class Article {
    public $title;
    public $date;
    public $postedBy;
    public $content;
    public $type;
    
    public function Article($params) {
        $this->title        = $params['title'];
        $this->date         = $params['date_added'];
        $this->postedBy     = $params['added_by'];
        $this->content      = $this->parseImagesInContent($params['content']);
        $this->type         = $params['type'];
    }

    public function parseImagesInContent($content) {
        $imgStrStart  = '<img';
        $imgStrEnd    = '>';
        $imgPosStart = 0;
        $imgPosEnd   = 0;

        while ( ($imgPosStart = strpos($content, $imgStrStart, $imgPosStart)) !== false ) {
            $imgPosEnd = strpos($content, $imgStrEnd, $imgPosStart);
            $img       = substr($content, $imgPosStart, ($imgPosEnd-$imgPosStart)+strlen($imgStrEnd) );

            $srcStrStart = "src='";
            $srcStrEnd   = '.png';
            $srcPosStart = 0;
            $srcPosEnd   = 0;

            $srcPosStart = strpos($img, $srcStrStart, $srcPosStart)+strlen($srcStrStart);
            $srcPosEnd   = strpos($img, $srcStrEnd, $srcPosStart);

            $src = substr($img, $srcPosStart, ($srcPosEnd-$srcPosStart)+strlen($srcStrEnd) );

            $flagStrStart = "flags/";
            $flagStrEnd   = '.';
            $flagPosStart = 0;
            $flagPosEnd   = 0;

            $flagPosStart = strpos($src, $flagStrStart, $flagPosStart)+strlen($flagStrStart);
            $flagPosEnd   = strpos($src, $flagStrEnd, $flagPosStart);

            $flag = substr($src, $flagPosStart, ($flagPosEnd-$flagPosStart) );

            $imgPath = ABSOLUTE_PATH . $src;

            if ( !file_exists($imgPath) ) {
                $imgPath = Functions::getImageFlag($flag);

                $content = str_replace($img, $imgPath, $content);
            }

            $imgPosStart = $imgPosStart + strlen($imgStrStart);
        }

        $content = str_replace('<br>', '', $content);

        return $content;
    }
}