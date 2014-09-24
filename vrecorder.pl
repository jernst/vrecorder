#!/usr/bin/perl
#
# Main loop for vrecorder

use strict;
use warnings;

use Config::Simple;
use Getopt::Long;
use Time::HiRes qw(usleep gettimeofday);

my $name;
my $url;
my $periodmillis;
my $loopminutes;
my $directory;
my $confFile;

my $parseOk = GetOptions(
    "name=s"         => \$name,
    "url=s"          => \$url,
    "periodmillis=s" => \$periodmillis,
    "loopminutes=s"  => \$loopminutes,
    "directory=s"    => \$directory,
    "conf=s"         => \$confFile );
unless( $parseOk ) {
    synopsisAndQuit();
}

if( $confFile ) {
    unless( -r $confFile ) {
        synopsisAndQuit( "Cannot read file $confFile" );
    }
    my $cfg = new Config::Simple( $confFile );

    unless( $name ) {
        $name = $cfg->param( 'name' );
    }
    unless( $url ) {
        $url = $cfg->param( 'url' );
    }
    unless( $periodmillis ) {
        $periodmillis = $cfg->param( 'periodmillis' );
    }
    unless( $loopminutes ) {
        $loopminutes = $cfg->param( 'loopminutes' );
    }
    unless( $directory ) {
        $directory = $cfg->param( 'directory' );
    }
}
if( !$name || !$url || !$directory ) {
    synopsisAndQuit( 'Must provide at least --name, --url and --directory as arguments or in config file' );
}
unless( -d $directory ) {
    die( 'Directory does not exist: ' . $directory );
}
if( $periodmillis && ( $periodmillis !~ m!^\d+$! || $periodmillis == 0 )) {
    die( 'periodmillis must be a positive integer' );
}
if( $loopminutes && ( $loopminutes !~ m!^-?\d+$! )) {
    die( 'loopminutes must be am integer integer' );
}
unless( $periodmillis ) {
    $periodmillis = 1000;
}
unless( $loopminutes ) {
    $loopminutes = -1;
}

my $purgeTimer = 0;
while( 1 ) {
    my( $startSec, $startMicroSec ) = gettimeofday();

    my $filePath = pathFromTime( $startSec, $startMicroSec );

    my @splitPath = split /\//, $filePath;
    for( my $i=0 ; $i<@splitPath ; ++$i ) {
        my $current = $directory;
        for( my $j=0 ; $j<$i ; ++$j ) {
            $current .= '/' . $splitPath[$j];
        }
        unless( -d $current ) {
            mkdir( $current );
        }
    }
    
    # get a pic
    my $picFile  = "$directory/$filePath";
    my $curlCmd  = "curl -s '$url' -o '$picFile'";
    `$curlCmd > /dev/null 2>&1`;

    if( ( -e $picFile ) && ( -s $picFile ) < 1000 ) { # sanity check
        unlink( $picFile );
    }

    # delete oldest if needed
    if( $loopminutes > 0 && $purgeTimer-- <= 0 ) {
        my $oldFilePath = pathFromTime( $startSec - 60 * $loopminutes - 60, 0 ); # one more minute, so we keep at least $loopminutes

        my @oldSplitPath = split /\//, $oldFilePath;
        pop @oldSplitPath; # get rid of filename

        my $dir = join( '/', $directory, @oldSplitPath );
        if( -d $dir ) {
            unlink( glob( "$dir/*" ));
        }

        for( my $i=@oldSplitPath-1 ; $i>=0 ; --$i ) {
            $dir = join( '/', $directory, @oldSplitPath[0..$i] );
            if( -d $dir ) {
                opendir( DIR, $dir );
                my @stillThere = readdir(DIR);
                close( DIR );
                if( @stillThere <= 2 ) {
                    rmdir( $dir );
                } else {
                    last; # don't need to check parent directories
                }
            }
        }
        $purgeTimer = int( 30000/$periodmillis ); # let's do this twice a minute
    }

    # wait
    my( $nowSec, $nowMicroSec ) = gettimeofday();

    my $delta = (( $nowSec - $startSec ) * 1000000 ) + $nowMicroSec - $startMicroSec;
    if( $delta < $periodmillis * 1000 ) {
        usleep( $periodmillis * 1000 - $delta );
    }
}

sub pathFromTime {
    my $ts    = shift;
    my $micro = shift;

    my ( $sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst ) = gmtime( $ts );
    my $ret = sprintf "%.4d/%.2d/%.2d/%.2d/%.2d/%.2d-%.3d.jpg", ($year+1900), ( $mon+1 ), $mday, $hour, $min, $sec, ( int( $micro / 1000 ) % 1000 );

    return $ret;
}
    
sub synopsisAndQuit {
    my $msg = shift;

    if( $msg ) {
        print STDERR "$msg\n";
    }
    print STDERR <<MSG;
Synopsis: $0 --name <cameraname> --url <jpgurl-with-auth> --directory <dir> [ --periodmillis <delay> ][ --loopminutes <minutes> ]
          $0 --conf <configfile>
MSG
    exit 0;
}

1;

