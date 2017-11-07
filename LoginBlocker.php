<?php
namespace ognyk\loginblocker;

use Yii;
use yii\base\Component;

class LoginBlocker extends Component
{
    private $time = 300; // 5 min
    
    private $wrong_login_number = 3;

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
        
        echo var_dump($blocker);
        
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
    
    private function getIp() 
    {
        $ip = $_SERVER['HTTP_CLIENT_IP'] ? $_SERVER['HTTP_CLIENT_IP'] : ($_SERVER['HTTP_X_FORWARDE‌​D_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
        
        return $ip;
    }
}