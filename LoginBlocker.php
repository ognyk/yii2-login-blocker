<?php
namespace ognyk\loginblocker;

use Yii;
use yii\base\Component;

class LoginBlocker extends Component
{
    /**
     * Time to block in seconds (default 5 min)
     *
     * @var integer
     */
    private $time = 300;
    
    
    /**
     * Number of wrong attempts (default 3 times)
     *
     * @var integer
     */
    private $wrong_login_number = 3;
    
    
    /**
     * Implement parameters
     *
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
            $blocker = [1, time()];
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
     * Check if user can login
     * 
     * @return boolean
     */
    public function check()
    {
        $blocker = Yii::$app->cache->get('login_blocker_' . $this->getIp());
        
        if ($blocker) {
            if (time() < $blocker[1]) {
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
}