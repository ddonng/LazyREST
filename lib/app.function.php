<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

function ss( $key )
{
	return isset($_SESSION[$key])?$_SESSION[$key]:false;
}


function ss_set( $key , $value )
{
	return $_SESSION[$key] = $value;
}


function is_login()
{
	if( isset( $_COOKIE['PHPSESSID'] ) )
	{
		session_start();
		return ss('uid') > 0;
	}
	
	return false;
}

function is_admin()
{
	return ss('ulevel') > 5 ;
}

function rjoin(  $sp , $str , $array )
{
	$ret = array();
	foreach( $array as $key => $value )
	{
		$ret[] = $str.trim($value , $str ).$str;
	}
	
	return join( $sp , $ret );
}

function has_saekv()
{
	if( defined('SAE_ACCESSKEY') && substr( SAE_ACCESSKEY , 0 , 4 ) == 'kapp' ) return false;
 	return in_array( 'SaeKV' , get_declared_classes() );
	//return false;
}

if( !has_saekv() ) @mkdir( AROOT. '__lr3_kv');

function kget( $key )
{
	if( has_saekv() )
	{
		$kv = new SaeKV();$kv->init();
		$value = $kv->get( $key );
		if(is_NULL($value) || $value == "")
			$value=temp_kget_out( $key );
		return $value;
	}
	else
	{
		$keyfile = AROOT. '__lr3_kv' . DS . 'kv-'.md5($key);
		return @unserialize( @file_get_contents($keyfile) );
	}
}

function kset( $key , $value )
{
	if( has_saekv() )
	{
		$kv = new SaeKV();$kv->init();
		//写一份到Mysql中
		if( temp_kset_into( $key , $value ) )
			return $kv->set( $key , $value );
	}
	else
	{
		$keyfile = AROOT. '__lr3_kv' . DS . 'kv-'.md5($key);
		return @file_put_contents($keyfile , serialize( $value )  );
	}
}

//若kvdb中无，则直接数据库中查找，写入kvdb后再return
function get_pri_name(  $table , $db=NULL )
{
	$pri_name=kget('primary_key_name_'.$table);
	if ($pri_name=="")
	{
		$pri_name=get_table_pri( $table , $db);
		kset( 'primary_key_name_'.$table , $pri_name);
	}
	return $pri_name;
}

function get_my_action_code( $table , $action )
{
	$my_code=kget( $table . "_" . $action ."_code");
	if($my_code=="")
	{
		$my_code=get_action_code( $table , $action);
		kset( $table . "_" . $action ."_code" , $my_code);
	}
	return $my_code;
}

function get_db_list( $db = NULL )
{
	if( $data = get_data("SHOW DATABASES" , $db) )
	{
		foreach( $data as $line )
		{
			if( substr( $line['Database'] , 0 , strlen( '__meta_' ) )  ==  '__meta_' ) continue;
			$ret[] = $line['Database'];
		}
		
		return $ret;
	}
	else
		return false;
}

function table_exists( $table , $db = NULL)
{
	$ret = false;
	if( $data = get_data("SHOW TABLES" , $db ) )
		foreach( $data as $line )
			if( strtolower( $table ) == strtolower(reset( $line )) ) $ret = true;
	
	return $ret;

}

function get_table_list( $db = NULL )
{
	if( $data = get_data("SHOW TABLES" , $db ) )
	{
		foreach( $data as $line )
		{
			if( substr( reset($line) , 0 , strlen( '__meta_' ) )  ==  '__meta_' ) continue;
			$ret[] = reset( $line );
		}
		
		return $ret;
	}
	else
		return false;
}

function get_fields_info( $table , $db = NULL )
{
	if( $data = get_data("SHOW COLUMNS FROM `" . $table . "`" , $db ) )
	{
		foreach( $data as $line )
		{
			$ret[] = $line;
		}
		
		return $ret;
	}
	else
		return false;
}

function get_fields( $table , $db = NULL )
{
	if( $data = get_data("SHOW COLUMNS FROM `" . $table . "`" , $db ) )
	{
		foreach( $data as $line )
		{
			$ret[] = $line['Field'];
		}
		
		return $ret;
	}
	else
		return false;
}



function get_field_info( $table , $field , $db = NULL )
{
	
	if( $data = get_data("SHOW COLUMNS FROM `" . $table . "`" , $db ) )
	{
		foreach( $data as $line )
		{
			if( $line['Field'] == $field  )
			{
				$line['Length'] = get_field_length( $line['Type'] );
				$line['Type'] = get_field_type( $line['Type'] );
				return  $line;
			}
		}
		
		return false;
	}
	
	return false;
}
