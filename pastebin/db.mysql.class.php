<?php
/**
 * $Project: Pastebin $
 * $Id: db.mysql.class.php,v 1.3 2006/04/27 16:20:06 paul Exp $
 * 
 * Pastebin Collaboration Tool
 * http://pastebin.com/
 *
 * This file copyright (C) 2005 Paul Dixon (paul@elphin.com)
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
 
/**
* Database handler
* Very simple, bare bones database handler - if your database isn't supported,
* write another version of this class and change the relevant line of the 
* config file to pull it in, i.e. for Postgres support, write a class in
* db.postgres.class.php and set $CONF['dbsystem']='postgres';
*
* All of the SQL used by the rest of the code is contained in here
*/
class DB
{
	var $dblink;
	var $dbresult;
	
	/**
	* Constructor - establishes DB connection
	*/
	function DB()
	{
		global $CONF;
		$this->dblink=mysql_pconnect(
			$CONF["dbhost"],
			$CONF["dbuser"],
			$CONF["dbpass"])
			or die("Unable to connect to database");
	
		mysql_select_db($CONF["dbname"], $this->dblink)
			or die("Unable to select database {$GLOBALS[dbname]}");

		mysql_query("SET NAMES latin1;");
	}
	
	
    /**
    * How many posts on domain $subdomain?
    * access public
    */
    function getPostCount($subdomain)
    {
    	$this->_query('select count(*) as cnt from pastebin where domain=?', $subdomain);
    	return $this->_next_record() ? $this->_f('cnt') : 0;
    }
    
    /**
    * Delete oldest $deletecount posts from $subdomain
    * access public
    */
    function trimDomainPosts($subdomain, $deletecount)
    {
    	//build a one-shot statement to delete old posts
		$sql='delete from pastebin where pid in (';
		$sep='';
		$this->_query("select * from pastebin where domain=? order by posted asc limit $deletecount", $subdomain);
		while ($this->_next_record())
		{
			$sql.=$sep.$this->_f('pid');
			$sep=',';
		}
		$sql.=')';
		
		//delete extra posts
		$this->_query($sql);	
    }
    
    /**
    * Delete all expired posts
    * access public
    */
    function deleteExpiredPosts()
    {
    	$this->_query("delete from pastebin where expires is not null and now() > expires");	
    }
    
    /**
    * Add post and return id
    * access public
    */
    function addPost($poster,$subdomain,$format,$code,$parent_pid,$expiry_flag)
    {
	//TODO: replace kludge by proper solution
	$badwords = array('groups.google.');

	foreach ($badwords as $badword) {
		echo $badword;
		if (strpos($code, $badword)) {
			return;
		}
	}

    	//figure out expiry time
    	switch ($expiry_flag)
    	{
    		case 'd';
    			$expires="DATE_ADD(NOW(), INTERVAL 1 DAY)";
    			break;
			case 'f';
				$expires="NULL";
			default:
			case 'm';
    			$expires="DATE_ADD(NOW(), INTERVAL 1 MONTH)";
    			break;
			
    		
    	}
    	
    	
    	$this->_query('insert into pastebin (poster, domain, posted, format, code, parent_pid, expires,expiry_flag, poster_ip) '.
				"values (?, ?, now(), ?, ?, ?, $expires, ?, ?)",
				$poster,$subdomain,$format,$code,$parent_pid, $expiry_flag, $_SERVER['REMOTE_ADDR']);	
		$id=$this->_get_insert_id();	
		
		//Oki, annonšons-le sur #Win
		$url = 'http://' . ($subdomain ? $subdomain . '.' : '') . 'pastebin.espace-win.org/' . $id;
		$message = "[PasteBin] $url :..: Format : $format :..: Auteur : $poster";
		//$this->ircQueue('SurfBoard', 'viperbroadcast', $message);
		//$this->ircQueue('SurfBoard', 'putquick', "PRIVMSG #Win :$message");
		
		
		return $id;
    }
    
    private function ircQueue ($bot, $commande, $arg1 = false, $arg2 = false, $arg3 = false) {
			$varg1 = ($arg1 === false) ? "''" : '?';
			$varg2 = ($arg2 === false) ? "''" : '?';
			$varg3 = ($arg3 === false) ? "''" : '?';
			$this->_query(
				"INSERT INTO IRC_Queue (Bot, Commande, Arg1, Arg2, Arg3) VALUES (?, ?, $varg1, $varg2, $varg3)", $bot, $commande, $arg1, $arg2, $arg3);
    }
    
     /**
    * Return entire pastebin row for given id/subdomdain
    * access public
    */
    function getPost($id, $subdomain)
    {
    	$this->_query('select *,date_format(posted, \'%a %D %b %H:%i\') as postdate '.
    		'from pastebin where pid=? and domain=?', $id, $subdomain);
    	if ($this->_next_record())
    		return $this->row;
    	else
    		return false;
		
    }
    
     /**
    * Return summaries for $count posts ($count=0 means all)
    * access public
    */
    function getRecentPostSummary($subdomain, $count)
    {
    	$limit=$count?"limit $count":"";
    	
    	$posts=array();
    	$this->_query("select pid,poster,unix_timestamp()-unix_timestamp(posted) as age, ".
			"date_format(posted, '%a %D %b %H:%i') as postdate ".
			"from pastebin ".
			"where domain=? ".
			"order by posted desc, pid desc $limit", $subdomain);
		while ($this->_next_record())
		{
			$posts[]=$this->row;	
		}
		
		return $posts;
    }
    
    /**
    * Get follow up posts for a particular post
    * access public
    */
    function getFollowupPosts($pid, $limit=5)
    {
    	//any amendments?
		$childposts=array();
		$this->_query("select pid,poster,".
			"date_format(posted, '%a %D %b %H:%i') as postfmt ".
			"from pastebin where parent_pid=? ".
			"order by posted limit $limit", $pid);
		while ($this->_next_record())
		{
			$childposts[]=$this->row;
		}
		
		return $childposts;	
    	
    }
    
   /**
    * Save formatted code for a post
    * access public
    */
    function saveFormatting($pid, $codefmt, $codecss)
    {
    	$this->_query("update pastebin set codefmt=?,codecss=? where pid=?",
    		$codefmt, $codecss, $pid);
	}
    
    
    	
	/**
	* execute query - show be regarded as private to insulate the rest of
	* the application from sql differences
	* @access private
	*/
	function _query($sql)
	{
		
		//been passed more parameters? do some smart replacement
		if (func_num_args() > 1)
		{
			//query contains ? placeholders, but it's possible the
			//replacement string have ? in too, so we replace them in
			//our sql with something more unique
			$q=md5(uniqid(rand(), true));
			$sql=str_replace('?', $q, $sql);
			
			$args=func_get_args();
			for ($i=1; $i<=count($args); $i++)
			{
				$sql=preg_replace("/$q/", "'".preg_quote(mysql_real_escape_string($args[$i]))."'", $sql,1);
				
			}
		
			//we shouldn't have any $q left, but it will help debugging if we change them back!
			$sql=str_replace($q, '?', $sql);
		}
		
		
		$this->dbresult=mysql_query($sql, $this->dblink);
		if (!$this->dbresult)
		{
			die("Query failure: ".mysql_error()."<br />$sql");
		}
		return $this->dbresult;
	}
	
	/**
	* get next record after executing _query
	* @access private
	*/
	function _next_record()
	{
		$this->row=mysql_fetch_array($this->dbresult);
		return $this->row!=FALSE;
	}
	
  	/**
	* get result column $field
	* @access private
	*/
	function _f($field)
    {
    	return $this->row[$field];
    }
 
 	/**
	* get last insertion id
	* @access private
	*/
	function _get_insert_id()
	{
		return mysql_insert_id($this->dblink);
	}
	
	/**
	* get last error
	* @access public
	*/
	function get_db_error()
	{
		return mysql_last_error();
    }
}
?>
