<?php

class SRPThemeSettings
{
    private $option_values = array();
    private $option_ids = array();

    private function getLibraryNameKey() { return 'library_name'; }
    public  function getLibraryName() { return $this->option_values[$this->getLibraryNameKey()]; }
    public  function setLibraryName($val) { $this->option_values[$this->getLibraryNameKey()] = $val; }
    
    private function getGoogleAnalyticsIDKey() { return 'google_analytics_id'; }
    public  function getGoogleAnalyticsID() { return $this->option_values[$this->getGoogleAnalyticsIDKey()]; }
    public  function setGoogleAnalyticsID($val) { $this->option_values[$this->getGoogleAnalyticsIDKey()] = $val; }

    private function getMaxReviewLengthKey() { return 'review_max_length'; }
    public  function getMaxReviewLength() { return $this->option_values[$this->getMaxReviewLengthKey()]; }
    public  function setMaxReviewLength($val) { $this->option_values[$this->getMaxReviewLengthKey()] = $val; }

    private function getHeaderFooterColorKey() { return 'color_header_footer'; }
    public  function getHeaderFooterColor() { return $this->option_values[$this->getHeaderFooterColorKey()]; }
    public  function setHeaderFooterColor($val) { $this->option_values[$this->getHeaderFooterColorKey()] = $val; }

    private function getHeaderImageUrlKey() { return 'header_img_url'; }
    public  function getHeaderImageUrl() { return $this->option_values[$this->getHeaderImageUrlKey()]; }
    public  function setHeaderImageUrl($val) { $this->option_values[$this->getHeaderImageUrlKey()] = $val; }

    private function getFooterImageUrlKey() { return 'footer_img_url'; }
    public  function getFooterImageUrl() { return $this->option_values[$this->getFooterImageUrlKey()]; }
    public  function setFooterImageUrl($val) { $this->option_values[$this->getFooterImageUrlKey()] = $val; }

    private function getSideColorKey() { return 'color_sides'; }
    public  function getSideColor() { return $this->option_values[$this->getSideColorKey()]; }
    public  function setSideColor($val) { $this->option_values[$this->getSideColorKey()] = $val; }

    private function getBodyColorKey() { return 'color_body'; }
    public  function getBodyColor() { return $this->option_values[$this->getBodyColorKey()]; }
    public  function setBodyColor($val) { $this->option_values[$this->getBodyColorKey()] = $val; }
    
    public function __construct()
    {
    }

    public function dbSelect()
    {
        global $wpdb;
        $themeopt_tablename = $wpdb->prefix . 'srp_themeopt';
        $query = $wpdb->prepare("SELECT id, option_name, option_value FROM $themeopt_tablename");
        
        $IDs = $wpdb->get_col($query, 0);
        if (count($IDs) == 14)
        {
            $option_name = $wpdb->get_col($query, 1);
            $option_value = $wpdb->get_col($query, 2);
            for ($i = 0; $i < count($IDs); $i++)
            {
                $this->option_values[$option_name[$i]] = $option_value[$i];
                $this->option_ids[$option_name[$i]] = $IDs[$i];
            }
        }
        else
        {
            $this->option_values['color_header_footer'] = '1E6088';
            $this->option_values['color_sides'] = '3D2D1E';
            $this->option_values['color_body'] = 'B5D1E6';
            $this->option_values['review_max_length'] = 500;
        }

        return true;
    }

    public function dbUpdate()
    {
        global $wpdb;
        $themeopt_tablename = $wpdb->prefix . 'srp_themeopt';

        $update_query = "UPDATE $themeopt_tablename SET option_value = %s WHERE id = %s";
        $insert_query = "INSERT INTO $themeopt_tablename (option_name, option_value) VALUES (%s, %s)";

        foreach ($this->option_values as $name => $val)
        {
            if (array_key_exists($name, $this->option_ids))
            {
                $wpdb->query($wpdb->prepare($update_query, $val, $this->option_ids[$name]));
            }
            else
            {
                $wpdb->query($wpdb->prepare($insert_query, $name, $val));
            }
        }

        unset($this->option_values);
        unset($this->option_ids);
        return $this->dbSelect();
    }
}
