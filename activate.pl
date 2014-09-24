#!/usr/bin/perl
#
# Activate and deactivate the vrecorder systemd service

use strict;
use warnings;

use UBOS::Utils;

if( 'install' eq $operation ) {
    my $name = $config->getResolve( 'installable.customizationpoints.name.value' );

    UBOS::Utils::myexec( 'sudo systemctl enable vrecorder@' . $name . ' 2> /dev/null' );
    UBOS::Utils::myexec( 'sudo systemctl start vrecorder@' . $name );
}
if( 'uninstall' eq $operation ) {
    my $name = $config->getResolve( 'installable.customizationpoints.name.value' );

    UBOS::Utils::myexec( 'sudo systemctl stop vrecorder@' . $name );
    UBOS::Utils::myexec( 'sudo systemctl disable vrecorder@' . $name . ' 2> /dev/null' );
}

1;

