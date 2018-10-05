<?php 
class Muc {
    public function getActiveLoginUsers() {
//    	$session_cache_expire = ini_get('session.cache_expire');
		
        $query = "SELECT  active_sessions.id,						
						users.username,
						users.name,
						users.last_login,
						active_sessions.ip,
						count(active_sessions.ip) as contador
						from users, active_sessions
						WHERE users.id = active_sessions.user_id
						GROUP BY active_sessions.ip, users.id";
		
        $result = DBUtil::getResultArray($query);
        		
        foreach ($result as $rs) {
			$ses_id = $rs['id'];
            //$id = $rs['uid'];
            $uname = $rs['username'];
            $name = $rs['name'];
            $last_login = $rs['last_login'];
			$contador=$rs['contador'];
			$ip = $rs['ip'];
		
			if($class == 'folder_row'){
		  		$class = 'odd folder_row';
		  	}
			else{
				$class = 'folder_row';
			}
			
			$last_login = explode(' ', $last_login);
			$fecha = $last_login[0];
			$hora = $last_login[1];
			$fecha = explode('-',$fecha);
			$fecha = $fecha[2] .'/'. $fecha[1] .'/'. $fecha[0];
			$last_login = $fecha .' '. $hora;
			
			$data[$ses_id] = array('ses_id'=>$ses_id, 'username'=>$uname,  'name'=>$name,'last_login'=>$last_login, 'ip'=>$ip, 'counter'=>$contador, 'class'=>$class);
			
        }
        
        return $data;
    }
}

