<?php

$servername = "localhost";
$username = "root";
$password = "";
$db = "vesselsdb";
$ip="";
$contentType="";

if ($_SERVER['REQUEST_METHOD'] == "GET") {
        //LOG USER REQUEST INFORMATION IN requestslog.txt FILE
        
        $ip=getUserIpAddr();
        logRequest();
        //IDENTIFY USER FROM DATABASE
        try {
                $conn = mysqli_connect($servername, $username, $password,$db);
                $sql = "SELECT id, userRequests FROM users WHERE ip=\"{$ip}\"";
                $result = mysqli_query($conn, $sql);
                    
                $id="";
                $userRequests=0;
                //IF USER EXISTS UPDATE HIS INFORMATION
                if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                                $id=$row["id"];
                                $userRequests=$row["userRequests"];
                        }
                        $userRequests = $userRequests+1;
                        if ($conn->connect_error) {
                                die("Connection failed: " . $conn->connect_error);
                        }
                        $sql = "UPDATE users SET userRequests={$userRequests}, lastTimestamp=".time()." WHERE id={$id}";
                        $conn->query($sql);
                        

                } 
                //IF USER DOESN'T EXIST INSERT IN DATABASE
                else {
                        $sql = "INSERT INTO users (ip, userRequests, firstTimestamp, lastTimestamp)
                                VALUES (\"".$ip."\", 1," .time().",".time().")";
                        mysqli_query($conn, $sql);
                        
                }
        } catch (mysqli_sql_exception $ex) {
                throw new Exception("Can't connect to the database! \n" . $ex);
        }
        //COMPARE lastTimestamp WITH firstTimestamp AND CHECK IF THE DIFFERENCE IS GREATER THAN 1 HOUR
        $firstTimestamp=0;
        $lastTimestamp=0;
        $timestampDifference=0;
        try {
                $conn = mysqli_connect($servername, $username, $password,$db);
                $sql = "SELECT id, firstTimestamp, lastTimestamp FROM users WHERE ip=\"{$ip}\"";
                $result = mysqli_query($conn, $sql);
                if (mysqli_num_rows($result) > 0) {
                        
                        while($row = mysqli_fetch_assoc($result)) {
                                $id=$row["id"];
                                $firstTimestamp=$row["firstTimestamp"];
                                $lastTimestamp=$row["lastTimestamp"];
                                $timestampDifference=$lastTimestamp-$firstTimestamp;
                                                                
                        }
                        
                }
            } catch (mysqli_sql_exception $ex) {
                throw new Exception("Can't connect to the database! \n" . $ex);
        }
        
        //CHECK IF THE DIFFERENCE IS GREATER THAN 1 HOUR AND RESET IF TRUE
        if($timestampDifference>=3600){
                try {
                        $userRequests = 1;
                        $sql = "UPDATE users SET userRequests={$userRequests}, lastTimestamp=".time().", firstTimestamp=".time()." WHERE id={$id}";
                                $conn->query($sql); 
                }catch (mysqli_sql_exception $ex) {
                        throw new Exception("Can't connect to the database! \n" . $ex);
                }
        }

        $sql="";
        //LIMIT USER TO 10 REQUESTS PER HOUR
        if($userRequests>10){
                echo ("Exceeded max requests. Try again later.");
                http_response_code(403);
        }
        //RESPOND TO THE USER IF HE HAS LESS THAN 10 REQUESTS
        else{
                
                $params  = explode('&', $_SERVER['QUERY_STRING']);
                //FILTERS
                foreach($params as $param){
                        //MMSI FILTER
                        if(startswith($param,"mmsi")){
                                $temp=explode('=', $param);                                
                                if(empty($sql)){
                                        $sql = "SELECT id, mmsi, status, stationId, speed, lon, lat, course, heading, rot, timestamp FROM vessels WHERE mmsi={$temp[1]}";
                                }else{
                                        $sql = $sql . " AND mmsi={$temp[1]}";
                                }                         
                        }
                        //LATITUDE RANGE FILTER
                        else if(startsWith($param,"lat")){

                                if(startsWith($param,"latMin"))
                                {
                                        $temp=explode('=', $param);
                                        if(empty($sql)){
                                                $sql = "SELECT id, mmsi, status, stationId, speed, lon, lat, course, heading, rot, timestamp FROM vessels WHERE lat>={$temp[1]}";
                                        }else{
                                                $sql = $sql . " AND lat>={$temp[1]}";
                                        }
                                }
                                if(startsWith($param,"latMax"))
                                {
                                        $temp=explode('=', $param);
                                        if(empty($sql)){
                                                $sql = "SELECT id, mmsi, status, stationId, speed, lon, lat, course, heading, rot, timestamp FROM vessels WHERE lat<={$temp[1]}";
                                        }else{
                                                $sql = $sql . " AND lat<={$temp[1]}";
                                        }
                                }
                                
                        }
                        //LONGITUDE RANGE FILTER
                        else if(startsWith($param,"lon")){

                                if(startsWith($param,"lonMin"))
                                {
                                        $temp=explode('=', $param);
                                        if(empty($sql)){
                                                $sql = "SELECT id, mmsi, status, stationId, speed, lon, lat, course, heading, rot, timestamp FROM vessels WHERE lon>={$temp[1]}";
                                        }else{
                                                $sql = $sql . " AND lon>={$temp[1]}";
                                        }
                                }
                                if(startsWith($param,"lonMax"))
                                {
                                        $temp=explode('=', $param);
                                        if(empty($sql)){
                                                $sql = "SELECT id, mmsi, status, stationId, speed, lon, lat, course, heading, rot, timestamp FROM vessels WHERE lon<={$temp[1]}";
                                        }else{
                                                $sql = $sql . " AND lon<={$temp[1]}";
                                        }
                                }
                        }
                        //TIME INTERVAL FILTER
                        else if(startsWith($param,"timestamp")){
                                if(startsWith($param, "timestampMin"))
                                {
                                        $temp=explode('=', $param);
                                        
                                        if(empty($sql)){
                                                $sql = "SELECT id, mmsi, status, stationId, speed, lon, lat, course, heading, rot, timestamp FROM vessels WHERE timestamp>={$temp[1]}";
                                        }else{
                                                $sql = $sql . " AND timestamp>={$temp[1]}";
                                        }
                                }
                                if(startsWith($param, "timestampMax"))
                                {
                                        $temp=explode('=', $param);
                                        if(empty($sql)){
                                                $sql = "SELECT id, mmsi, status, stationId, speed, lon, lat, course, heading, rot, timestamp FROM vessels WHERE timestamp<={$temp[1]}";
                                        }else{
                                                $sql = $sql . " AND timestamp<={$temp[1]}";
                                        }
                                }
                        }else if(startsWith($param, "contentType")){
                                $temp=explode('=', $param);
                                $contentType = $temp[1];
                        }
                }
                
                //EXECUTE REQUEST QUERY AND RETURN REQUEST RESULTS TO THE USER
                try {
                        $conn = mysqli_connect($servername, $username, $password,$db);
                        $result = mysqli_query($conn, $sql);
                        $resultsArray=array();
                        if (mysqli_num_rows($result) > 0) {                        
                                while($row = mysqli_fetch_assoc($result)) {
                                        //echo $row;
                                        array_push($resultsArray,$row);
                                }
                        }
                    } catch (mysqli_sql_exception $ex) {
                        die("Can't connect to the database! \n" . $ex);
                }
                
                
                
                //Content-Type:application/json
                if($contentType=="json"){
                        header('Content-Type: application/json');
                        header("Content-Disposition:attachment;filename=results.json");
                        echo json_encode($resultsArray);
                }
                //Content-Type:application/xml
                else if($contentType=="xml"){
                        header("Content-Type:application/xml"); 
                        header("Content-Disposition:attachment;filename=results.xml");
                        $xml_data = new SimpleXMLElement("<?xml version=\"1.0\"?><root></root>");
                        array_to_xml($resultsArray,$xml_data);
                        $xml_file = $xml_data->asXML('results.xml');
                        readfile('results.xml');
                }
                //Content-Type:text/csv
                else if($contentType=="csv"){
                        header("Content-Type:text/csv"); 
                        header("Content-Disposition:attachment;filename=results.csv");
                        $output = fopen("php://output",'w') or die("Can't open php://output"); 
                        fputcsv($output, array('id','mmsi','status','stationId','speed','lon','lat','course','heading','rot','timestamp'));
                        foreach($resultsArray as $item) {
                                fputcsv($output, $item);
                        }
                        fclose($output) or die("Can't close php://output");
                }
                //Content-Type: application/vnd.api+json
                else if($contentType=="vnd_api_json"){
                        header('Content-Type: application/vnd.api+json');
                        header("Content-Disposition:attachment;filename=results.json");
                        $object = (object) ['data' => $resultsArray];
                        echo json_encode($object);
                        
                }
                
                else{
                        echo("Invalid content type provided. Please include a valid content type as an argument.");
                }                              
                http_response_code(200);
        }
        
        
} else {
        http_response_code(405);
}
//LOG USER REQUEST INFORMATION IN requestslog.txt FILE
function logRequest(){
        $req_dump="";
        foreach (getallheaders() as $name => $value) { 
                $req_dump = $req_dump . PHP_EOL . "$name: $value"; 
        }
        $req_dump = $req_dump.PHP_EOL.$_SERVER['REQUEST_METHOD'].PHP_EOL.$_SERVER['REQUEST_URI'];
        $req_dump = $req_dump.PHP_EOL.getUserIpAddr().PHP_EOL;
        $fp = fopen('requestslog.txt', 'a');
        fwrite($fp, $req_dump);
        fclose($fp);
}
//GET USER IP ADDRESS
function getUserIpAddr(){
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
                $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
                $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
                $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
                $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
                $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
                $ipaddress = getenv('REMOTE_ADDR');
        else
                $ipaddress = 'UNKNOWN';
        if($ipaddress == '::1'){
                $ipaddress = "127.0.0.1";
        }
        return $ipaddress;
}
    
//CHECK IF A STRING STARTS WITH ANOTHER STRING
function startsWith ($string, $startString) 
{ 
        $len = strlen($startString); 
        return (substr($string, 0, $len) === $startString); 
}

//function defination to convert array to xml
function array_to_xml($array, &$xml_user_info) {
        foreach($array as $key => $value) {
                if(is_array($value)) {
                        if(!is_numeric($key)){
                                $subnode = $xml_user_info->addChild("$key");
                                array_to_xml($value, $subnode);
                        }else{
                                $subnode = $xml_user_info->addChild("item$key");
                                array_to_xml($value, $subnode);
                        }
                }else {
                        $xml_user_info->addChild("$key",htmlspecialchars("$value"));
                }
        }
    }
    
?>