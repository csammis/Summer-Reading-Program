<?php

class SRPMessages
{
    private $message_values = array();
    private $message_ids = array();
    private $message_dirty = array();

    private function getFooterTextKey() { return 'srp_footertext'; }
    public  function getFooterText()
    {
        // This one is special because it can contain HTML
        $retval = $this->message_values[$this->getFooterTextKey()];
        $retval = preg_replace('/&quot;/', '"', $retval);
        $retval = preg_replace('/&gt;/', '>', $retval);
        $retval = preg_replace('/&lt;/', '<', $retval);
        $retval = preg_replace('/<iframe[*.?]>/', '', $retval);
        $retval = preg_replace('/<script[*.?]>/', '', $retval);

        return $retval;
    }

    public  function setFooterText($val)
    {
        $this->message_values[$this->getFooterTextKey()] = $val;
        $this->message_dirty[$this->getFooterTextKey()] = 1;
    }
    
    private function getRegistrationAgreementKey() { return 'srp_regagreement'; }
    public  function getRegistrationAgreement() { return $this->message_values[$this->getRegistrationAgreementKey()]; }
    public  function setRegistrationAgreement($val)
    {
        $this->message_values[$this->getRegistrationAgreementKey()] = $val;
        $this->message_dirty[$this->getRegistrationAgreementKey()] = 1;
    }

    private function getHourlyEmailKey() { return 'srp_hourlyemail'; }
    public  function getHourlyEmail() { return $this->message_values[$this->getHourlyEmailKey()]; }
    public  function setHourlyEmail($val)
    {
        $this->message_values[$this->getHourlyEmailKey()] = $val;
        $this->message_dirty[$this->getHourlyEmailKey()] = 1;
    }

    private function getWeeklyEmailKey() { return 'srp_weeklyemail'; }
    public  function getWeeklyEmail() { return $this->message_values[$this->getWeeklyEmailKey()]; }
    public  function setWeeklyEmail($val)
    {
        $this->message_values[$this->getWeeklyEmailKey()] = $val;
        $this->message_dirty[$this->getWeeklyEmailKey()] = 1;
    }

    private function getHourlyPrizeNoticeKey() { return 'srp_hourlynotice'; }
    public  function getHourlyPrizeNotice() { return $this->message_values[$this->getHourlyPrizeNoticeKey()]; }
    public  function setHourlyPrizeNotice($val)
    {
        $this->message_values[$this->getHourlyPrizeNoticeKey()] = $val;
        $this->message_dirty[$this->getHourlyPrizeNoticeKey()] = 1;
    }


    public function __construct()
    {
    }

    public function dbSelect()
    {
        global $wpdb;
        $messages_tablename = $wpdb->prefix . 'srp_messages';
        $query = $wpdb->prepare("SELECT id, message_name, message_value FROM $messages_tablename");
        
        $IDs = $wpdb->get_col($query, 0);
        if (count($IDs) == 5)
        {
            $message_name = $wpdb->get_col($query, 1);
            $message_value = $wpdb->get_col($query, 2);
            for ($i = 0; $i < count($IDs); $i++)
            {
                $this->message_values[$message_name[$i]] = stripslashes($message_value[$i]);
                $this->message_ids[$message_name[$i]] = $IDs[$i];
            }
        }
        else
        {
            $this->message_values[$this->getFooterTextKey()] = $this->getDefaultMessageContent($this->getFooterTextKey());
            $this->message_values[$this->getRegistrationAgreementKey()] = $this->getDefaultMessageContent($this->getRegistrationAgreementKey());
            $this->message_values[$this->getHourlyEmailKey()] = $this->getDefaultMessageContent($this->getHourlyEmailKey());
            $this->message_values[$this->getWeeklyEmailKey()] = $this->getDefaultMessageContent($this->getWeeklyEmailKey());
            $this->message_values[$this->getHourlyPrizeNoticeKey()] = $this->getDefaultMessageContent($this->getHourlyPrizeNoticeKey());
        }

        unset($this->message_dirty);
        return true;
    }

    public function dbUpdate()
    {
        global $wpdb;
        $messages_tablename = $wpdb->prefix . 'srp_messages';

        $update_query = "UPDATE $messages_tablename SET message_value = %s WHERE id = %s";
        $insert_query = "INSERT INTO $messages_tablename (message_name, message_value) VALUES (%s, %s)";

        foreach ($this->message_dirty as $name => $is_dirty)
        {
            if (array_key_exists($name, $this->message_ids))
            {
                $wpdb->query($wpdb->prepare($update_query, $this->message_values[$name], $this->message_ids[$name]));
            }
            else
            {
                $wpdb->query($wpdb->prepare($insert_query, $name, $this->message_values[$name]));
            }
        }

        unset($this->message_values);
        unset($this->message_ids);
        unset($this->message_dirty);

        return $this->dbSelect();
    }

    private function getDefaultMessageContent($message)
    {
        $retval = 'Unknown message';

        if ($message == 'srp_hourlyemail')
        {
            $retval  = "Good work!\n\nYou've read enough pages or minutes to earn a %%prizename%%. ";
            $retval .= "Please come pick up your prize at %%libraryname%% by August 1. Remember to bring your verification code: %%prizecode%%\n\n";
            $retval .= "-- %%libraryname%%\n\n";
            $retval .= "Note: Prizes may be substituted in the event that the supply runs out.";
        }
        else if ($message == 'srp_hourlynotice')
        {
            $retval  = 'If you have not picked up your prizes, please come to the library and collect ';
            $retval .= 'them by August 1.  Remember to bring the verification code listed next to each prize.';
        }
        else if ($message == 'srp_weeklyemail')
        {
            $retval  = "We've picked your recent review as a winning entry in our weekly drawing!\n\n";
            $retval .= "Please come pick up your prize at %%libraryname%% by August 1. Remember to bring your verification code: 985.\n\n";
            $retval .= "-- %%libraryname%%";
            //cstodo this verification code should be a prize setting
        }
        else if ($message == 'srp_regagreement')
        {
            $retval = 'I am a resident of Johnson County, Kansas, and am able to pick up any prizes I win at %%libraryname%%.';
        }
        else if ($message == 'srp_footertext')
        {
            $retval  = "<p>\n";
            $retval .= '<a href="http://www.library.org/">Library Main Page</a>&nbsp;|&nbsp;' . "\n";
            $retval .= '<a href="http://catalog.library.org">Library Catalog</a>&nbsp;|&nbsp;' . "\n";
            $retval .= '<a href="http://www.library.org/teens/">Library Teens\' Page</a><br /><br />' . "\n\n";
            
            $retval .= '<strong>Library Main</strong>: 201 E Park St., Everytown, KS 00000 (555.555.5555)<br />' . "\n";
            $retval .= '<strong>Library Branch</strong>: 12990 S South Rd., Everytown, KS 00000 (555.555.5556)<br /><br />' . "\n";
            $retval .= '</p>' . "\n";
            $retval .= '<p>&nbsp;</p>' . "\n";
            $retval .= '<p>All art and graphics are used by permission of their respective creators and may not be reproduced or copied in any way.</p>';
        }

        return $retval;
    }
}
