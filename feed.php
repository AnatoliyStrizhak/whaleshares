<?php

if (isset($_REQUEST["usr"]) && $_REQUEST["usr"]!="")
{
    $usr=$_REQUEST["usr"];
    $limit=10;

    if (isset($_REQUEST["limit"]) && $_REQUEST["limit"]!="")
    {
        $limit=$_REQUEST["limit"];
    }

    function truncate($text, $length) 
    {
        $length = abs((int)$length);
        if(strlen($text) > $length) 
        {
            $text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $text);
        }
        return($text);
    }


    header('Content-type: text/xml;charset=UTF-8');
    echo '<?xml version="1.0" encoding="UTF-8" ?>'; 
?>

    <rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
    >


    <channel>
	<title>Whaleshares RSS feed</title>
	<link>http://brehen-sobaken.ru/whaleshares/feed.php?usr=<?php echo $usr;?></link>
	<atom:link href="http://brehen-sobaken.ru/whaleshares/feed.php?usr=<?php echo $usr;?>"  rel="self" type="application/rss+xml" />    
	<description>Simple RSS feed for whaleshares.io</description>
	<language>en</language>

<?php
    //Get last posts from each author I follow
    $r=file_get_contents('https://api.whaleshares.io/rest2jsonrpc/database_api/get_discussions_by_feed?params=[{%22tag%22:%22'.$usr.'%22,%22limit%22:'.$limit.'}]');

    $res=json_decode($r,$assoc=true);


    foreach($res['result'] as $key=>$val)
    {

        $body=strip_tags($val["body"],"");
        $link="http://whaleshares.io/@".$val["author"]."/".$val["permlink"];

        if($body!="")
        {

?>

    <item>
    <title><![CDATA[<?php echo $val["title"]; ?>]]></title>
    <guid isPermaLink="true"><?php echo $link; ?></guid>
    <link><?php echo $link; ?></link>
    <dc:creator><![CDATA[<?php echo $val["author"]; ?>]]></dc:creator>
    <description><![CDATA[<?php echo $body; ?>]]></description>
    <pubDate><?php echo $val["created"]; ?></pubDate>
    </item>    

<?php
}
    }
?>
    </channel>
    </rss>
<?php


}
else
{
    echo "Dont forget to enter your Whaleshares login as url parameter. For example ?usr=astrizak<p>By default limit of items in feed set to 10. If you want more just set &limit=20 param to url</p>";
}
?>