<?php

  function htmlspecialchars_array(array $array) {
    foreach($array as $key => $val) {
      $array[$key] = (is_array($val)) ? htmlspecialchars_array($val) : htmlspecialchars($val);
    }
    return $array;
  }

  /////////////////////////////////////////////////
  //  Meteo
  /////////////////////////////////////////////////

  function meteo () {

    $meteo  = '<div id="cont_MzgxMzd8NXwzfDF8NHwwMDAwMDB8MXxGRkZGRkZ8Y3wx"><div id="spa_MzgxMzd8NXwzfDF8NHwwMDAwMDB8MXxGRkZGRkZ8Y3wx"><a id="a_MzgxMzd8NXwzfDF8NHwwMDAwMDB8MXxGRkZGRkZ8Y3wx" href="http://www.meteocity.com/france/cras_v38137/" rel="nofollow" target="_blank" style="color:#333;text-decoration:none;">Météo Cras</a> © meteocity.com</div><script type="text/javascript" src="http://widget.meteocity.com/js/MzgxMzd8NXwzfDF8NHwwMDAwMDB8MXxGRkZGRkZ8Y3wx"></script></div>';

    return $meteo;
  }

  /////////////////////////////////////////////////
  //  TS3
  /////////////////////////////////////////////////

  function ts3 () {

    $host_ip    = '';
    $host_port  = '30033';
    $socket     = 0;
    $socket     = @fsockopen($host_ip, $host_port, $errno, $errstr, 3);
    $html       = '';

    if($socket && !$errno){
      require_once("./TS3_PHP_Framework-1.1.12/libraries/TeamSpeak3/TeamSpeak3.php");
      $ts3_ServerInstance = TeamSpeak3::factory("serverquery://login:password@$host_ip:10011/");
      $ts3_VirtualServer  = $ts3_ServerInstance->serverGetById(1);

      $html .= '<img width="200px" src="pict/triton_ts3.png" style="margin-bottom : 10px;">';
      $html .= '<table cellspacing="0px" cellpadding="0px">';
      foreach($ts3_VirtualServer->clientList() as $client){
        if($client["client_unique_identifier"] == 'serveradmin'){continue;}
        $html .= '  <tr>';
        $html .= '    <td valign="middle">';
        $html .= '      <img style="width : 20px;" src="./TS3_PHP_Framework-1.1.12/images/viewer/'.$client->getIcon().'.png" alt="">';
        $html .= '    </td>';
        $html .= '    <td valign="middle" class="ts3_user">';
        $html .= '      '.htmlspecialchars($client);
        $html .= '    </td>';
        $html .= '  </tr>';
      }
      $html .= '</table>';
    }

    return $html;
  }

  /////////////////////////////////////////////////
  //  PING
  /////////////////////////////////////////////////

  function ping () {
    $hosts    = array();
    $hosts_ip = array(
                    'machine1'    => array('0.0.0.0', '0'),
                    'machine2'    => array('0.0.0.0', '0')
                  );

    foreach($hosts_ip as $hostname => $host_data){
      $host_ip    = $host_data[0];
      $host_port  = $host_data[1];
      $socket     = 0;
      $socket     = @fsockopen($host_ip, $host_port, $errno, $errstr, 3);
      if($socket && !$errno){$hosts[$hostname] = 'up';}
      else{$hosts[$hostname] = 'down';}
    }

    $html  = '';
    $html .= '<table cellspacing="10px">';
    $c=0;
    foreach($hosts as $hostname => $host_status){
      if($c == 0){$html .= '<tr>';}
      $html .= '<td class="ping ping_'.$host_status.'">'.$hostname.'</td>';
      $c++;
      if($c == 2){$c = 0; $html .= '</tr>';}
    }
    if($c != 0){$html .= '</tr>';}
    $html .= '</table>';

    return $html;
  }

  /////////////////////////////////////////////////
  //  VPN PPTPD
  /////////////////////////////////////////////////

  function vpn () {

    $datas = vpn_parseData ("/home/jarvis/vpn/vpn_oberon.log");

    $html  = '';

    if(sizeof($datas) > 0){
      $html .= '<table cellspacing="0px">';
      foreach($datas as $data){
        $html .= '<tr>';
        $html .= '<td valign="middle"><img class="vpn" src="pict/vpn.png"></td><td class="vpn">'.$data[0].'</td>';
        $html .= '</tr>';
      }
      $html .= '</table>';
    }

    return $html;
  }

  function vpn_parseData ($stat_file) {
    $datas = array();
    if(filemtime($stat_file) < time()-10){return $datas;}
    $stats = fopen($stat_file, 'r');
    while (($line = fgets($stats)) !== false) {
      $explode_line = str_word_count($line, 1, "0123456789.");
      $datas[]  = $explode_line;
    }
    fclose($stats);
    return $datas;
  }

  /////////////////////////////////////////////////
  //  IFSTAT
  /////////////////////////////////////////////////

  function imagickHisto ($max, $eth = '', $up_down = 'down') {

    $datas = parseData ("/home/jarvis/ifstat/eth0.log", $up_down);

    $width   = 304;
    $height  = 100;
    $padding = 1;
    $ticks   = 5;

    $background_color = '#000';
    $axes_color       = '#555';
    if($up_down == 'down'){
      $data_color       = '#1D1';
    }
    else{
      $data_color       = '#D11';
    }

    $nb_values        = $width - 2*$padding - 2;
    $max_value        = $height - 2*$padding - 4;

    $nb_datas         = sizeof($datas);
    $trim             = $nb_values - $nb_datas;
    if($trim < 0){$trim = 0;}

    $image = new Imagick();
    $image ->newImage( $width, $height, new ImagickPixel($background_color) );

    $draw  = new ImagickDraw();

    $draw->setStrokeColor( new ImagickPixel($axes_color) );

    $xx1    = $padding;
    $xy1    = $height - $padding - 1;
    $xx2    = $width - $padding - 1;
    $xy2    = $xy1;
    $yx1    = $xx1;
    $yy1    = $xy1;
    $yx2    = $yx1;
    $yy2    = $padding;
    $half_y = $height/2;
    $half_x = $width/2;

    $draw->line  ( $xx1, $xy1, $xx2, $xy2 );
    $draw->line  ( $yx1, $yy1, $yx2, $yy2 );

    $draw->line  ( $yx1, $yy2, $yx1+$ticks, $yy2 );
    $draw->line  ( $yx1, $half_y, $yx1+$ticks, $half_y );

    $draw->setStrokeColor( new ImagickPixel($data_color) );

    $first_x = $xx1 + 1 + $trim;
    $last_x  = $xx2 - 1;
    $first_y = $xy1 - 1;
    $last_y  = $yy2 + 1;

    for($i=0;$i<$nb_values;$i++){
      if(isset($datas[$i])){
        $value   = $datas[$i]*$max_value/$max;
        $value_y = $first_y - $value;
        $value_x = $first_x + $i;
        $draw->line  ( $value_x, $first_y, $value_x, $value_y );
      }
    }

    $image->drawImage( $draw );

    // TEXT
    $text_draw = new ImagickDraw();
    $text_draw->setFillColor($axes_color);
    $text_draw->setFontSize( 12 );
    $image->annotateImage($text_draw, $half_x-20, $padding+10, 0, "$eth - $up_down");

    $image->setImageFormat( "png" );
    header( "Content-Type: image/png" );
    echo $image;
    exit;
  }

  function parseData ($stat_file, $up_down) {
    $datas = array();
    if(filemtime($stat_file) < time()-10){return $datas;}
    $stats = fopen($stat_file, 'r');
    while (($line = fgets($stats)) !== false) {
      $explode_line = str_word_count($line, 1, "0123456789.");
      if($up_down == 'down') {
        $datas[]  = $explode_line[0];
      }
      else{
        $datas[]  = $explode_line[1];
      }
    }
    fclose($stats);
    $datas = array_slice($datas, -300);
    return $datas;
  }

?>
