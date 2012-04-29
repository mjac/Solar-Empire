<?php

require_once('inc/user.inc.php');


/*
#Created: 12/12/01
#Adapted for SE: 18/2/02
#Disclaimer: This search engine is provided as is. No warranty, and its probably your own fault if it breaks.
#Enjoy!
*/
function search_the_db($term,$can_do_stop,$db_to_search,$search_page,$field_in_db){
	global $db_name,$user;

	//split the entered terms into seperate components
	$alpha_1231 = preg_split ("/\s/", trim($term));
	$beta_4543 = count($alpha_1231);
	$new_search = trim($term);

	//remove stopwords unless otherwise specified
	if(!$can_do_stop && $beta_4543 > 1) {

		//dump the stoplist into an array.
		$stoplist = array('a','b','by','c','d','e','e.g.','eg','f','for','from','g','h','h.','had','has','have','i','ie','i.e.','if','in','is','it','it\'d','it\'ll','it\'s','its','j','k','l','m','my','n','no','o','of','oh','ok','okay','or','p','q','r','s','see','so','t','that','the','these','they','this','those','to','too','u','v','w','which','who','why','will','with','would','x','y','you','your','z');

		//remove stopwords from entered text
		$amount = count($stoplist);
		$stop_count = 0;
		$stop_words_removed=0;

		//while loop that does searching for stopwords
		while ($stop_count < $amount){
			if(preg_match("/^".preg_quote($stoplist[$stop_count])."\s/",$new_search) || preg_match("/(?i)\s".preg_quote($stoplist[$stop_count])."$/",$new_search) || preg_match("/(?i)\s".preg_quote($stoplist[$stop_count])."\s/",$new_search)){
				$new_search = preg_replace("/(?i)^".preg_quote($stoplist[$stop_count])."\s/","",$new_search);
				$new_search = preg_replace("/(?i)\s".preg_quote($stoplist[$stop_count])."$/","",$new_search);
				$new_search = preg_replace("/(?i)\s".preg_quote($stoplist[$stop_count])."\s/"," ",$new_search);
				$stop_list_list[$stop_words_removed] = $stoplist[$stop_count];
				$stop_words_removed++; //stopwords removed from entered term.
			}
			$stop_count++; //total stopwords in stopword list;
		}
		//puts commas in list of removed stopwords
		$count = 0;

		while($count < $stop_words_removed){
			if($count != $stop_words_removed-1){
				$stop_str .= $stop_list_list[$count].", ";
			} else {
				$stop_str .= $stop_list_list[$count];
			} //end if
			$count++;
		} //end while
	} //end stopword removal

	//Split the remaining search into seperate terms
	$keywords = preg_split ("/\s/", $new_search);
	$num_terms = count($keywords);

	//if a user enters only stop words
	if($num_terms < 1){
		$stop_count = 0;
		$stop_words_removed=0;
		$new_search = trim($term);
	}

	//create the sql query
	$sql_query = ""; //clear query text
	$c1 = 0; //clear counter
	foreach($keywords as $value){
		//used to create a lowercase set of search terms.
		$keywords_2[$c1] = strtolower($value);

		//add to sql_query text.
		if($c1 > 0){ //determine if already got an entry in query. If so, then need an ||.
			$sql_query .= "|| ${field_in_db} REGEXP '$value'";
		} else {
			$sql_query .= " ${field_in_db} REGEXP '$value'";
		}
		if($db_to_search == "diary"){
			$sql_query .= "&& login_id = '$user[login_id]'";
		}

		$c1++;
	} //end foreach of search terms

	//the mysql query which finds the results
	db("select timestamp,${field_in_db} from ${db_name}_${db_to_search} where".$sql_query." order by timestamp desc");
	$news = dbr();

	$primary_counter = 0; //used to ensure goes around for each keyword;

	//loop through all results found
	while($news){
		//ensure array entry is clear, and enter initial data.
		$search_results_text[$primary_counter] = $news[$field_in_db];
		$search_results_timestamp[$primary_counter] = $news['timestamp'];
		$search_results_finds[$primary_counter] = 0;
		$search_results_score[$primary_counter] = 0;

		//loop through the lowercase search terms (as substr_count is case dependent).
		foreach($keywords_2 as $value){
			if(preg_match("/".preg_quote($value)."/i",$search_results_text[$primary_counter])) {
				$search_results_finds[$primary_counter]++;
				$search_results_score[$primary_counter] += (substr_count(strtolower($search_results_text[$primary_counter]), $value) -1) * 2;
				$search_results_text[$primary_counter] = eregi_replace("$value","<font color=lime>$value</font>",$search_results_text[$primary_counter]);
			}
		}

		$primary_counter++;
		$news = dbr();
	} //end term list while


	//determine if any results were found.
	if (!empty($search_results_text)) { //results found
		//nifty little function allows many arrays to be sorted together, and keeps information in them in the same place in relation to each other. Excellent for search tech.
		array_multisort($search_results_finds, SORT_DESC, SORT_NUMERIC, $search_results_score, SORT_DESC, SORT_NUMERIC, $search_results_timestamp, SORT_DESC, SORT_NUMERIC, $search_results_text);

		$ret_str = "<FORM method=POST action='$filename' name=search_form>";
		$ret_str .= "New Search: <input type=text name=term size=20 value='$term'>";
		$ret_str .= " - <INPUT type=submit value=Search></form><p>";
		$ret_str .= "<br>Shown below are the results for your search using terms (<b class=b1>$term</b>) in ranked, then chronological order:<br><br>";

		//stopwords where removed
		if ($stop_words_removed > 0){
			$stop_search_txt = urlencode($term);
			$ret_str .= "<i>Stop-words</i> were removed from your search. These consisted of: <b>".$stop_str."</b>.<br>Click <a href=${search_page}.php?term=$stop_search_txt&can_do_stop=1>here</a> to run a search with the stop-words included.<p>";
		}
		$num = 0; //keep track of where in the arrays the system is.

		//make table for output
		$ret_str .= make_table(array("",""));

		//cycle through the results for the final output.
		while($var = each($search_results_text)) {
			if($num == 0){//first result, determine how many of the keywords where found.
				$keep_track = $search_results_finds[$num];
				$k_temp_tracker = count($keywords);

				if($k_temp_tracker > $keep_track){ //only some of the words where found.
					$follow_through = 2;
				} else { //all keywords make an appearance in result.
					$follow_through = 1;
				}
			}

			//if all keywords are found, then cycle through only results that have all keywords in them, otherwise cycle through all results.
			if($follow_through == 1 && $search_results_finds[$num] != $keep_track){
				break;
			}

			$ret_str .= quick_row("<b>".date("M d - H:i",$search_results_timestamp[$num]),$var[1]);
			$num++;
		}
		//end table, then return results.
		$ret_str .= "</table><br>";
		return $ret_str;

	} else { //no results found
		return "<br>No entries of <b class=b1>$term</b> were found. Please broaden your search.<br><br>";
	}

} //end search_the_db function



$text = "";

$rs = '<p><a href=location.php>Back to the Star System</a><br>';

#news search
if(isset($term)) {
	$rs = '<p><a href=news.php>Back to News</a><br>';

	$text = search_the_db($term, $can_do_stop, "news", "news", "headline");
	print_page("News Search",$text);

}

db("select count(*) from ${db_name}_news");
$news_ents = dbr(0);

if (!isset($news_posts_show)) {
	$news_posts_show = $user_options['news_back'];
}

if (!isset($prev)) {
	db("select * from ${db_name}_news order by timestamp desc LIMIT 0, $news_posts_show");
	$text .= "Last <b>$news_posts_show</b> posts to the news.<br>";
	$prev2 = $news_posts_show;
	$prev = $news_posts_show;
} else {
	$prev3 = $prev - $news_posts_show;
	$prev2 = $prev + $news_posts_show;
	$text .= "<a href=news.php?prev=$prev3>Back to posts $prev3 to $prev</a><p>";
	$text .= "Posts $prev to $prev2 of the news.<br>";
	db("select * from ${db_name}_news order by timestamp desc LIMIT $prev, $news_posts_show");
}


$text .= "<h2>Search the News:</h2>\n";

$text .= <<<END
<form method="post" action="news.php">
	<p><input type="text" name="term" size="20" />
	<input type="submit" value="Search" /></p>
</form>
<h2>There are $news_ents[0] entries</h2>
<table class="simple">
	<tr>
		<th>Date</th>
		<th>Headline</th>
	</tr>

END;

while ($news = dbr(1)) {
	$text .= "\t<tr>\n\t\t<td>" . date("M d - H:i",$news['timestamp']) .
	 "</td>\n\t\t<td>" . $news['headline'] . "</td>\n\t</tr>\n";
}

$text .= "</table>\n";

if($news_ents[0] > $prev2) {
	$prev3 = $prev2 + $news_posts_show;
	$text .= "<p><a href=news.php?prev=$prev2>Posts $prev2 to $prev3</a>";
} else {
	$prev3 = $prev2 - $news_posts_show;
	if($news_ents[0] > $news_posts_show){
		$text .= "<a href=news.php?prev=0>Back to start</a><p>";
	}
}

print_page('News',$text);

?>