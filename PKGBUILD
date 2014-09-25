developer="http://upon2020.com/"
url="https://github.com/jernst/vrecorder"
maintainer=$developer
pkgname=$(basename $(pwd))
pkgver=0.1
pkgrel=1
pkgdesc="Trivially simple webcam recorder"
depends=('perl-config-simple')
arch=('any')
license=('GPL')
options=('!strip')
md5sums=('76ef522124a23e3d65fe65de00ae487a')

package() {
# Manifest
    mkdir -p $pkgdir/var/lib/ubos/manifests
    install -m0644 $startdir/ubos-manifest.json $pkgdir/var/lib/ubos/manifests/${pkgname}.json

# Place for config files
    mkdir -p $pkgdir/etc/vrecorder

# Place for data files
    mkdir -p $pkgdir/var/lib/vrecorder

# Code
    mkdir -p $pkgdir/usr/share/$pkgname/
    install -m0755 $startdir/{activate,vrecorder}.pl $pkgdir/usr/share/$pkgname/

# Web
    install -m0755 $startdir/index.php $pkgdir/usr/share/$pkgname/
    install -m0644 $startdir/style.css $pkgdir/usr/share/$pkgname/

# Templates
    install -m0644 $startdir/conf.php.tmpl $pkgdir/usr/share/$pkgname/
    install -m0644 $startdir/conf.tmpl     $pkgdir/usr/share/$pkgname/
    install -m0644 $startdir/htaccess.tmpl $pkgdir/usr/share/$pkgname/

# systemd service
    mkdir -p $pkgdir/usr/lib/systemd/system/
    install -m0644 $startdir/vrecorder@.service $pkgdir/usr/lib/systemd/system/
}

