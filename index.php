<?php
require_once( 'conf.php' );

$request = substr( getenv( 'REQUEST_URI' ), strlen( $conf['context'] ));

function indent( $indent ) {
    return str_repeat( ' ', $indent );
}

function is_img( $file ) {
    $lastDot = strrchr( $file, '.' );
    if( $lastDot == FALSE ) {
        return FALSE;  
    }   
    if( $lastDot == '.png' || $lastDot == '.jpg' || $lastDot == '.gif' ) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function listYear( $containsYearsDir, $indent = 2 ) {
    $ret = 0;
    $isFirst = TRUE;

    $years = filesInDir( $containsYearsDir );
    foreach( $years as $year ) {
    
        $containsMonthsDir = "$containsYearsDir/$year";
        $months = filesInDir( $containsMonthsDir );

        foreach( $months as $month ) {

            $containsDaysDir = "$containsMonthsDir/$month";
            $days = filesInDir( $containsDaysDir );

            foreach( $days as $day ) {

                $containsHoursDir = "$containsDaysDir/$day";
                            
                if( $isFirst ) {
                print indent( $indent ) . "<dl>\n";
                    $isFirst = FALSE;
                }
                print indent( $indent+1 ) . "<dt>$year/$month/$day:</dt>\n";
                print indent( $indent+1 ) . "<dd>\n";

                if( is_dir( $containsHoursDir )) {
                    $ret += listDay( $year, $month, $day, $containsHoursDir, $indent+1 );
                } else {
                    print "&mdash;";
                }
                
                print indent( $indent+1 ) . "</dd>\n";
            }
        }
    }
    if( !$isFirst ) {
        print indent( $indent ) . "</dl>\n";
    }
    return $ret;
}

function listDay( $year, $month, $day, $containsHoursDir, $indent ) {
    $ret = 0;

    $hours = filesInDir( $containsHoursDir );
    foreach( $hours as $hour ) {

        $nImages = 0;
        $containsMinsDir = "$containsHoursDir/$hour";

        $minutes = filesInDir( $containsMinsDir );
        foreach( $minutes as $minute ) {

            $containsFilesDir = "$containsMinsDir/$minute";

            $files = filesInDir( $containsFilesDir );
            foreach( $files as $file ) {

                $fullFile = "$containsFilesDir/$file";
                if( is_img( $fullFile )) {
                    ++$nImages;
                }
            }
        }
        print "<p>$hour:00: <a href=\"$year/$month/$day/$hour/\">$nImages images</a></p>\n";
        $ret += $nImages;
    }
    return $ret;
}

function filesInDir( $dir ) {
    $ret = array();
    if( is_dir( $dir ) && $handle = opendir( $dir )) {
        while( false !== ( $file = readdir( $handle ))) {
            if( $file == '.' || $file == '..' ) {
                continue;
            }
            $ret[] = $file;
        }
        closedir( $handle );

        sort( $ret );
    }
    return $ret;
}

function showFilesInHourDir( $year, $month, $day, $hour ) {
    global $conf;

    $hourDir   = $conf['directory'] . "/$year/$month/$day/$hour";
    $picPrefix = $conf['context']   . "/img/$year/$month/$day/$hour";

    $minutes = filesInDir( $hourDir );
    foreach( $minutes as $minute ) {
    
        print "  <h2>$year/$month/$day $hour:$minute:00 UTC</h2>\n";

        $minuteDir = "$hourDir/$minute";
        $files = filesInDir( $minuteDir );

        foreach( $files as $file ) {
            print "   <img src='$picPrefix/$minute/$file'>\n";
        }
    }
}

?>
<html>
 <head>
  <link rel="stylesheet" type="text/css" href="<?= $conf['context'] ?>/style.css"/>
  <title>vrecorder: <?= $conf['name'] ?></title>
 </head>
 <body>
  <h1>Camera: <?= $conf['name'] ?></h1>
<?php
if( $request == '/' ) {
    if( listYear( $conf['directory' ] ) == 0 ) {
        print "   <p>No files found.</p>\n";
    }
} elseif( preg_match( "/^\/(\d\d\d\d)\/(\d\d)\/(\d\d)\/(\d\d)\/$/", $request, $matches )) {
    $year  = $matches[1];
    $month = $matches[2];
    $day   = $matches[3];
    $hour  = $matches[4];

    showFilesInHourDir( $year, $month, $day, $hour );

} else {
    print "<p>Invalid request.</p>\n";
}
?>
  <div class="footer">
   Vrecorder (C) 2014, Johannes Ernst. <a href="https://github.com/jernst/vrecorder">On Github</a>,
   pull requests appreciated.
  </div>
 </body>
</html>
