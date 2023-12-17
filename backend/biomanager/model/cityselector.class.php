<?php 

/**
 * CitySelector
 *
 * класс менеджмента города на сайте Biogumus.pro
 * используется библиотека SypexGeo для определения локализации 
 * 
 * @author Gregory Rozenbaum <bitard3d@gmail.com>
 * @package biomanager
 * @version 1.0.0
 */
 
class CitySelector {
    
    private $sessionKey = 'biogumus';
    private $ver = 12;
    private $modx; 
    
    // СФО, где порог доставки выше 
    private $SFO = array(
        "RU-OMS",
        "RU-TOM",
        "RU-ALT",
        "RU-KEM",
        "RU-KK",
        // "RU-KYA", // Красноярский
        "RU-TY",
        "RU-NVS",
        "RU-KDA",
        "RU-TYU",
        "RU-KHM",
        "RU-STA",
        "RU-KO"
    );
    
    // самара
    private $samara = "RU-SAM";
    private $samaraid = "499099";

    // регионы которые есть бесплатная доставка
    private $regions = array(
        "RU-MOW",
        "RU-SPE",
        "RU-AST",
        "RU-BA",
        "RU-BEL",
        "RU-BRY",
        "RU-VLA",
        "RU-VLG",
        "RU-VGG",
        "RU-VOR",
        "RU-KLU",
        "RU-KIR",
        //"RU-KO",
        "RU-KOS",
        "RU-KGN",
        "RU-KRS",
        "RU-LEN",
        "RU-LIP",
        "RU-ME",
        "RU-MO",
        "RU-MOS",
        "RU-NIZ",
        "RU-NGR",
        "RU-ORE",
        "RU-ORL",
        "RU-PNZ",
        "RU-PER",
        "RU-ROS",
        "RU-SAR",
        "RU-SVE",
        "RU-SE",
        "RU-SMO",
        "RU-TAM",
        "RU-TA",
        "RU-TUL",
        "RU-UD",
        "RU-ULY",
        "RU-CHE",
        "RU-CU",
        "RU-YAR",
        "RU-IVA"
    );
    
    public function __construct( modX &$modx ){ 
        $this->modx = &$modx;
        // $corePath = MODX_CORE_PATH . 'components/biomanager/';
        // $assetsUrl = MODX_ASSETS_URL . 'components/biomanager/';
    }

    public function saveToUser($location) {
        $user = $this->modx->user;
    }
    
    public function checkLocation() {
        
        $c = isset($_SESSION[$this->sessionKey]['location']) && isset($_SESSION[$this->sessionKey]['regiontype']);
        $c = $c && $_SESSION[$this->sessionKey]['ver'] == $this->ver;
        
        // if not set
        if (!$c) {
            $user = $this->modx->user;
            if ($user->isAuthenticated('web')) {
                $this->getLocationFromUser($user);
            } else {
                $info = $this->detectCity();
                $this->saveLocation($info);
            }
            
            $_SESSION[$this->sessionKey]['ver'] = $this->ver;
        }

    }

    public function getLocationFromUser($user) {
        $profile = $user->getOne('Profile');
        $city = $profile->get('city');
        $state = $profile->get('state');

        if (!empty($city) && !empty($state)) {
            $location = $this->selectLocation($city, $state);
            $this->saveLocation($location);
        } else {
            $info = $this->detectCity();
            
            $this->saveLocation($info);

            $profile->set('city', $this->getCityCode());
            $profile->set('state', $this->getRegionCode());
            $profile->save();

        }
    }

    private function selectLocation($cityid, $iso) {
        $q = "SELECT * from `sxgeo_regions` WHERE country='RU' AND sort>0 AND iso='" . $iso . "'";
        $r = $this->modx->query($q);
        while ($re = $r->fetch(PDO::FETCH_ASSOC)) {
            $regions[] = $re;
        }

        $reg = $regions[0];

        $q = "SELECT * from `sxgeo_cities` WHERE id='" . $cityid . "'";
        $r = $this->modx->query($q);
        while ($re = $r->fetch(PDO::FETCH_ASSOC)) {
            $cs[] = $re;
        }

        return array(
            'city'=>$cs[0],
            'region'=>$reg
        );
    }

    public function showRegionsList() {
        $q = "SELECT * from `sxgeo_regions` WHERE country='RU' AND sort>0 ORDER BY `sort` DESC, `name_ru` ASC";
        $r = $this->modx->query($q);
        $regions = array();

        while ($re = $r->fetch(PDO::FETCH_ASSOC)) {
            $regions[] = $re;
        }

        return $regions;
    }

    public function showCitiesByRegion($id) {
        $q = "SELECT * from `sxgeo_cities` WHERE region_id='" . $id . "' ORDER BY `sort` ASC, `name_ru` ASC";
        $r = $this->modx->query($q);
        $cities = array();
        while ($re = $r->fetch(PDO::FETCH_ASSOC)) {
            $cities[] = $re;
        }
        return $cities;
    }
    
    private function detectCity() {

        global $modx;

        require_once($modx->config['base_path'] . "/sxgeo/SxGeo.php");
        $base = $modx->config['base_path'] . "/sxgeo/SxGeoCity.dat";

        $SxGeo = new SxGeo($base);
        $ip = $_SERVER['REMOTE_ADDR'];
        $info = $SxGeo->getCityFull($ip);

        if ( isset($info['city']['id']) && ($info['city']['id'] > 0) && ($info['country']['iso'] == "RU")) {
            //echo "city ok 222222222222222222222222222222222222222222";

        } else {
            $info = $SxGeo->getCityFull('80.85.247.1');
            ///echo "MSK";
        }

        unset($SxGeo);

        return $info;

    }
    
    private function setRegionType($info) {
        
        $rt = 0;
        if (strcmp($info['region']['iso'], $this->samara) == 0) {
            if ($info['city']['id'] == $this->samaraid) {
                $rt = 1;
            } else {
                $rt = 2;
            }

        } else {
            if (in_array($info['region']['iso'], $this->SFO)) {
                $rt = 3;
            }
            if (in_array($info['region']['iso'], $this->regions)) {
                $rt = 2;
            }
        }
        
        $_SESSION[$this->sessionKey]['regiontype'] = $rt;
    }
    
    public function saveLocation($info) {
        
        $this->setLocation($info);
        $this->setRegionType($info);
        $this->setUserCheck(1);
        return "success";
    
    }

    public function setUserCheck(int $val) {
         $_SESSION[$this->sessionKey]['usercheck'] = $val;
    }
    
    public function getUsercheck() {
        return $_SESSION[$this->sessionKey]['usercheck'];
    }
    
    public function setLocation($location) {
        $_SESSION[$this->sessionKey]['location'] = $location;
    }
    
    public function getLocation() {
        return $_SESSION[$this->sessionKey]['location'];
    }
    
    public function getRegionType() {
        return  $_SESSION[$this->sessionKey]['regiontype'];   
    }
    
    public function getCityName() {
        return $this->getLocation()['city']['name_ru'];
    }
    
    public function getCityCode() {
        return $this->getLocation()['city']['id'];
    }
    
    public function getRegionName() {
        return $this->getLocation()['region']['name_ru'];
    }
    
    public function getRegionCode() {
        return $this->getLocation()['region']['iso'];
    }

}