<?php
require_once './simple_html_dom.php';

if (isset($_REQUEST["usr"]) && $_REQUEST["usr"]!="")
{
    $usr=$_REQUEST["usr"];

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
        <description>Simple RSS feed for whaleshares.io</description>
        <language>en</language>

<?php

    $h = file_get_html("https://whaleshares.io/@".$usr."/feed");

    $links=array();
    $titles=array();
    $dates=array();
    $authors=array();
    $bodys=array();


    foreach( $h->find('.articles__h2 a') as $res)
    {
        $link="https://whaleshares.io".$res->href;
        array_push($links, $link);

        array_push($titles, $res->plaintext);
    }

    foreach( $h->find('.updated') as $r)
    {
        array_push($dates, $r->title);
    }

    foreach( $h->find('.author') as $a)
    {
        array_push($authors, $a->plaintext);
    }

    foreach( $h->find('.PostSummary__body') as $b)
    {
        array_push($bodys, $b->plaintext);
    }


    for($i=0; $i<count($authors); $i++)
    {
?>
        <item>
        <title><![CDATA[<?php echo $titles[$i]; ?>]]></title>
        <guid isPermaLink="true"><?php echo $links[$i]; ?></guid>
        <dc:creator><![CDATA[<?php echo $authors[$i]; ?>]]></dc:creator>
        <description><![CDATA[<?php echo $bodys[$i]; ?>]]></description>
        <pubDate><?php echo date('r', strtotime($dates[$i])); ?></pubDate>
        </item>

<?php
    }
?>
    </channel>
    </rss>
<?php
}
else
{
    echo "<p>Dont forget to enter your Whaleshares login as url parameter. For example ?usr=astrizak</p>";
}
?>

