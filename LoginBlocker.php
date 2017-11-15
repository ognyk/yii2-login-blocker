<?php
namespace ognyk\loginblocker;

use Yii;
use yii\base\Component;

class LoginBlocker extends Component
{
    /**
     * Time to block/ban user in seconds (default 5 min)
     * @var integer
     */
    private $time = 300;
    
    /**
     * Number of wrong attempts (default 3 times)
     * @var integer
     */
    private $wrong_login_number = 3;
    
    /**
     * List of mails
     * @var array
     */
    private $mails = [];
    
    /**
     * Mail subject
     * Mark {ip} will be replace by user IP
     * @var string
     */
    private $mail_subject = 'LoginBlocker - This IP "{ip}" tried to login too many times.';
    
    /**
     * Mail content
     * Mark {ip} will be replace by user IP
     * Mark {date} will be replace by date
     * Mark {params} will be replace by user custom params
     * @var string
     */
    private $mail_content = '<b>LoginBlocker</b><br/>User IP: {ip}<br/>Date: {date}<br/>{params}';
    
    /**
     * Sender name
     * @var string
     */
    private $mail_sender_name = 'LoginBlocker';
    
    /**
     * Sender mail
     * @var string
     */
    private $mail_sender_mail = 'no-reply@mail.com';
    
    /**
     * Database name
     * @var string
     */
    private $database_name = null;
    
    /**
     * List of database columns
     * @var array
     */
    private $database_columns = [];
    
    /**
     * Implement parameters
     * @param array $params
     */
    public function __construct($params = [])
    {
        if (isset($params['time'])) {
            $this->time = $params['time'];
        }
        if (isset($params['wrong_login_number'])) {
            $this->wrong_login_number = $params['wrong_login_number'];
        }
        if (isset($params['mail']['mails'])) {
            $this->mails = $params['mail']['mails'];
        }
        if (isset($params['mail']['subject'])) {
            $this->mail_subject = $params['mail']['subject'];
        }
        if (isset($params['mail']['content'])) {
            $this->mail_content = $params['mail']['content'];
        }
        if (isset($params['mail']['sender']['name'])) {
            $this->mail_sender_name = $params['mail']['sender']['name'];
        }
        if (isset($params['mail']['sender']['mail'])) {
            $this->mail_sender_mail = $params['mail']['sender']['mail'];
        }
        if (isset($params['database']['name'])) {
            $this->database_name = $params['database']['name'];
        }
        if (isset($params['database']['columns'])) {
            $this->database_columns = $params['database']['columns'];
        }
    }
    
    /**
     * Block IP if wrong login or password
     * 
     * @return boolean
     */
    public function block()
    {
        $blocker = Yii::$app->cache->get('login_blocker_' . $this->getIp());
        
        if ($blocker === false) {
            $blocker = [1, time(), 0];
        } else { 
            $blocker[0]++;
            
            if ($blocker[0] == $this->wrong_login_number) {
                $blocker[1] = time() + $this->time; 
            }
        }
        
        Yii::$app->cache->set('login_blocker_' . $this->getIp(), $blocker, $this->time);

        return true;
    }
    
    /**
     *
     * Check if user can login
     * 
     * @param array $params     Custom array params f.e. ['Username' => 'Cesar V']
     * @return boolean
     */
    public function check($params = [])
    {
        $blocker = Yii::$app->cache->get('login_blocker_' . $this->getIp());
        
        if ($blocker) {
            if (time() < $blocker[1]) {
                if ($blocker[2] == 0) {
                    $time = $blocker[1] - time();
                    $blocker[2] = 1;
                    
                    \Yii::$app->cache->set('login_blocker_' . $this->getIp(), $blocker, $time);
                    
                    // Insert to database
                    $this->insertToDatabase($params);
                    
                    // Send mails
                    $this->sendMails($params);
                }
                
                return false;
            }
        }

        return true;
    }
    
    /**
     * Get user IP
     * 
     * @return string
     */
    private function getIp() 
    {
        $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
        
        return $ip;
    }
    
    /**
     * Send mails
     * 
     * @param array $params     Custom array params f.e. ['Username' => 'Cezar V']
     */
    private function sendMails($params = [])
    {
        $params_string = '';
        foreach ($params as $key => $param) {
            $params_string .= $key . ': ' . $param . '<br/>';
        }
        
        $subject = str_replace('{ip}', $this->getIp(), $this->mail_subject);
        $content = str_replace('{ip}', $this->getIp(), $this->mail_content);
        $content = str_replace('{date}', date('Y-m-d H:i:s'), $content);
        $content = str_replace('{params}', $params_string, $content);
        
        foreach($this->mails as $mail) {
            \Yii::$app->mailer->compose()
                ->setHtmlBody($content)
                ->setTextBody(strip_tags($content))
                ->setFrom([$this->mail_sender_mail => $this->mail_sender_name])
                ->setTo($mail)
                ->setSubject($subject)
                ->send();
        }
    }
    
    /**
     * Insert result to database
     * 
     * @param array $params
     */
    private function insertToDatabase($params = [])
    {
        if ($this->database_name && count($this->database_columns) > 0) {
            $table_values = [];
            $table_params = [];
            
            foreach ($params as $key => $param) {
                $table_params['{params.' . $key . '}'] = $param;
            }
            
            foreach ($this->database_columns as $key => $value) {
                $value = str_replace('{ip}', $this->getIp(), $value);
                $value = str_replace('{date}', date('Y-m-d H:i:s'), $value);
                
                if(isset($table_params[$value])) {
                    $value = str_replace($value, $table_params[$value], $value);
                }
                
                $table_values[$key] = $value;
            }
            
            \Yii::$app->db->createCommand()->insert($this->database_name, $table_values)->execute();
        }
    }
}