<?php

class SRPThemeSettings
{
    private $option_values = array();
    private $option_ids = array();

    public function getLibraryName() { return $this->option_values['library_name']; }
    
    public function getGoogleAnalyticsID() { return $this->option_values['google_analytics_id']; }

    public function getMaxReviewLength() { return $this->option_values['review_max_length']; }

    public function getHeaderFooterColor() { return $this->option_values['color_header_footer']; }

    public function getHeaderImageUrl() { return $this->option_values['header_img_url']; }

    public function getFooterImageUrl() { return $this->option_values['footer_img_url']; }

    public function getSideColor() { return $this->option_values['color_sides']; }

    public function getBodyColor() { return $this->option_values['color_body']; }
    
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

            //cstodo insert all defaults
        }

        return true;
    }
}
