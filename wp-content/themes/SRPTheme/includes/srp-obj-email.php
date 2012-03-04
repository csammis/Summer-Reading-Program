<?php

class SRPEmailSettings
{
    private $option_values = array();
    private $option_ids = array();
    private $option_dirty = array();

    private function getGoogleAccountNameKey() { return 'gmail_account_name'; }
    public  function getGoogleAccountName() { return $this->option_values[$this->getGoogleAccountNameKey()]; }
    public  function setGoogleAccountName($val)
    {
        $this->option_values[$this->getGoogleAccountNameKey()] = $val;
        $this->option_dirty[$this->getGoogleAccountNameKey()] = 1;
    }
    
    private function getGoogleAccountPassKey() { return 'gmail_account_pass'; }
    public  function getGoogleAccountPass() { return $this->option_values[$this->getGoogleAccountPassKey()]; }
    public  function setGoogleAccountPass($val)
    {
        $this->option_values[$this->getGoogleAccountPassKey()] = $val;
        $this->option_dirty[$this->getGoogleAccountPassKey()] = 1;
    }

    private function getGoogleAccountSendAsKey() { return 'gmail_send_email_as'; }
    public  function getGoogleAccountSendAs() { return $this->option_values[$this->getGoogleAccountSendAsKey()]; }
    public  function setGoogleAccountSendAs($val)
    {
        $this->option_values[$this->getGoogleAccountSendAsKey()] = $val;
        $this->option_dirty[$this->getGoogleAccountSendAsKey()] = 1;
    }

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

        unset($this->option_dirty);
        return true;
    }

    public function dbUpdate()
    {
        global $wpdb;
        $themeopt_tablename = $wpdb->prefix . 'srp_themeopt';

        $update_query = "UPDATE $themeopt_tablename SET option_value = %s WHERE id = %s";
        $insert_query = "INSERT INTO $themeopt_tablename (option_name, option_value) VALUES (%s, %s)";

        foreach ($this->option_dirty as $name => $is_dirty)
        {
            if (array_key_exists($name, $this->option_ids))
            {
                $wpdb->query($wpdb->prepare($update_query, $this->option_values[$name], $this->option_ids[$name]));
            }
            else
            {
                $wpdb->query($wpdb->prepare($insert_query, $name, $this->option_values[$name]));
            }
        }

        unset($this->option_values);
        unset($this->option_ids);
        unset($this->option_dirty);

        return $this->dbSelect();
    }
}
