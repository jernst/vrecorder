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
    if( $containsYearsHandle = opendir( $containsYearsDir )) {
        while( false !== ( $year = readdir( $containsYearsHandle ))) {
            if( $year == '.' || $year == '..' ) {
                continue;
            }
            $containsMonthsDir = "$containsYearsDir/$year";
            if( is_dir( $containsMonthsDir ) && $containsMonthsHandle = opendir( $containsMonthsDir )) {
                while( false !== ( $month = readdir( $containsMonthsHandle ))) {
                    if( $month == '.' || $month == '..' ) {
                        continue;
                    }
                    $containsDaysDir = "$containsMonthsDir/$month";
                    if( is_dir( $containsDaysDir ) && $containsDaysHandle = opendir( $containsDaysDir )) {
                        while( false !== ( $day = readdir( $containsDaysHandle ))) {
                            if( $day == '.' || $day == '..' ) {
                                continue;
                            }
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
                        closedir( $containsDaysHandle );
                    }
                }
                closedir( $containsMonthsHandle );
            }
        }
        closedir( $containsYearsHandle );
        if( !$isFirst ) {
            print indent( $indent ) . "</dl>\n";
        }
    }
    return $ret;
}

function listDay( $year, $month, $day, $containsHoursDir, $indent ) {
    $ret = 0;
    if( $containsHoursHandle = opendir( $containsHoursDir )) {
        while( false !== ( $hour = readdir( $containsHoursHandle ))) {
            if( $hour == '.' || $hour == '..' ) {
                continue;
            }
            $nImages = 0;
            $containsMinsDir = "$containsHoursDir/$hour";
            if( is_dir( $containsMinsDir ) && $containsMinsHandle = opendir( $containsMinsDir )) {
                while( false !== ( $min = readdir( $containsMinsHandle ))) {
                    if( $min == '.' || $min == '..' ) {
                        continue;
                    }
                    $containsFilesDir = "$containsMinsDir/$min";
                    if( is_dir( $containsFilesDir ) && $containsFilesHandle = opendir( $containsFilesDir )) {
                        while( false !== ( $file = readdir( $containsFilesHandle ))) {
                            if( $file == '.' || $file == '..' ) {
                                continue;
                            }
                            $fullFile = "$containsFilesDir/$file";
                            if( is_img( $fullFile )) {
                                ++$nImages;
                            }
                        }
                        closedir( $containsFilesHandle );
                    }
                }
                closedir( $containsMinsHandle );
            }
            print "<p>$hour:00: <a href=\"$year/$month/$day/$hour/\">$nImages images</a></p>\n";
            $ret += $nImages;
        }
    }
    closedir( $containsHoursHandle );

    return $ret;
}

function showFilesInHourDir( $year, $month, $day, $hour ) {
    global $conf;

    $hourDir   = $conf['directory'] . "/$year/$month/$day/$hour";
    $picPrefix = $conf['context'] . "/img/$year/$month/$day/$hour";

    if( is_dir( $hourDir ) && $hourDirHandle = opendir( $hourDir )) {
        while( false !== ( $min = readdir( $hourDirHandle ))) {
            if( $min == '.' || $min == '..' ) {
                continue;
            }
            print "  <h2>$year/$month/$day $hour:$min:00 UTC</h2>\n";

            $minDir = "$hourDir/$min";
            if( is_dir( $minDir ) && $minDirHandle = opendir( $minDir )) {
                while( false !== ( $file = readdir( $minDirHandle ))) {
                    if( $file == '.' || $file == '..' ) {
                        continue;
                    }
                    print "   <img src='$picPrefix/$min/$file'>\n";
                }
                closedir( $minDirHandle );
            }
        }
        closedir( $hourDirHandle );
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
