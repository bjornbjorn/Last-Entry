<?php

$plugin_info = array(
		'pi_name'			=> 'Last Entry',
		'pi_version'		=> '1.0',
		'pi_author'			=> 'Bjorn Borresen',
		'pi_author_url'		=> 'http://www.bybjorn.com/',
		'pi_description'	=> 'Get the last entry date from one or more weblogs.',
		'pi_usage'			=> Last_entry::usage()
	);

class Last_entry
{
  var $return_data = "";

  function Last_entry()
  {
    global $TMPL;
    global $DB;
    global $LOC;

    $format = $TMPL->fetch_param('format');
    $allow_future = ($TMPL->fetch_param('allow_future_entries') == 'yes');
    
    if($format == '')
    {
    	$format = "%Y-%m-%d";	// default
    }
    $weblogs = "";
    $weblogs_arr = explode("|", $TMPL->fetch_param('weblogs'));
    foreach($weblogs_arr as $blog_name) {
    	$weblogs .= "'$blog_name',";
    }
    $weblogs = substr($weblogs, 0, strlen($weblogs)-1);
    
    $sql = "SELECT entry_date FROM exp_weblogs w, exp_weblog_titles t where t.weblog_id=w.weblog_id AND w.blog_name IN(".$weblogs.") ";
	if(!$allow_future)
	{
		$sql .= "AND t.entry_date < ".$LOC->now." "; 
	}
	$sql .= "ORDER BY entry_date DESC LIMIT 0,1";
    
    $query = $DB->query($sql);    
	$row = $query->result;
    	
    $date_matches = $LOC->fetch_date_params($format);
        
    $val = $format;
	foreach ($date_matches as $dvar)
	{
		$val = str_replace($dvar, $LOC->convert_timestamp($dvar, $row['0']['entry_date'], TRUE), $val);		
	}											
						   
    $this->return_data = $val;
    
  }

// ----------------------------------------
//  Plugin Usage
// ----------------------------------------

	function usage()
	{
	ob_start(); 
	?>
	
	Get the last entry date from one or more weblogs.
	
	Usage:
	
	{exp:last_entry weblogs="default_site|my_second_weblog" format="%Y-%m-%d"}
	
	If you'd like to find future entries as well add "allow_future_entries='yes'" to the syntax above.

	<?php
	$buffer = ob_get_contents();
		
	ob_end_clean(); 
	
	return $buffer;
	}
	/* END */


}
// END CLASS