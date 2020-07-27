<?php
// Gallery extension, https://github.com/datenstrom/yellow-extensions/tree/master/source/gallery

class YellowGallery {
    const VERSION = "0.8.7";
    public $yellow;         // access to API

    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("galleryStyle", "photoswipe");
    }
    
    // Handle page content of shortcut
    public function onParseContentShortcut($page, $name, $text, $type) {
        $output = null;
        if ($name=="gallery" && ($type=="block" || $type=="inline")) {
            list($pattern, $style, $size) = $this->yellow->toolbox->getTextArguments($text);
            if (empty($style)) $style = $this->yellow->system->get("galleryStyle");
            if (empty($size)) $size = "100%";
            if (empty($pattern)) {
                $files = $this->yellow->media->clean();
            } else {
                $images = $this->yellow->system->get("coreImageDirectory");
                $files = $this->yellow->media->index(true, true)->match("#$images$pattern#");
            }
            if ($this->yellow->extension->isExisting("image")) {
                if (count($files)) {
                    $page->setLastModified($files->getModified());
                    $output = "<div class=\"".htmlspecialchars($style)."\" data-fullscreenel=\"false\" data-shareel=\"false\"";
                    if (substru($size, -1, 1)!="%") $output .= " data-thumbsquare=\"true\"";
                    $output .= ">\n";
                    foreach ($files as $file) {
                        list($widthInput, $heightInput) = $this->yellow->toolbox->detectImageInformation($file->fileName);
                        list($src, $width, $height) = $this->yellow->extension->get("image")->getImageInformation($file->fileName, $size, $size);
                        $caption = $this->yellow->language->isText($file->fileName) ? $this->yellow->language->getText($file->fileName) : "";
                        $output .= "<a href=\"".$file->getLocation(true)."\"";
                        if ($widthInput && $heightInput) $output .= " data-size=\"".htmlspecialchars("{$widthInput}x{$heightInput}")."\"";
                        if (!empty($caption)) $output .= " data-caption=\"".htmlspecialchars($caption)."\"";
                        $output .= ">";
                        $output .= "<img src=\"".htmlspecialchars($src)."\" width=\"".htmlspecialchars($width)."\" height=\"".
                            htmlspecialchars($height)."\" alt=\"".basename($file->getLocation(true))."\" title=\"".
                            basename($file->getLocation(true))."\" />";
                        $output .= "</a> \n";
                    }
                    $output .= "</div>";
                } else {
                    $page->error(500, "Gallery '$pattern' does not exist!");
                }
            } else {
                $page->error(500, "Gallery requires 'image' extension!");
            }
        }
        return $output;
    }

    // Handle page extra data
    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name=="header") {
            $extensionLocation = $this->yellow->system->get("coreServerBase").$this->yellow->system->get("coreExtensionLocation");
            $output = "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"{$extensionLocation}gallery.css\" />\n";
            $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$extensionLocation}gallery-photoswipe.min.js\"></script>\n";
            $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$extensionLocation}gallery.js\"></script>\n";
        }
        return $output;
    }
}