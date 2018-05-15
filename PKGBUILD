developer="http://upon2020.com/"
url="https://github.com/jernst/vrecorder"
maintainer=$developer
pkgname=$(basename $(pwd))
pkgver=0.4
pkgrel=1
pkgdesc="Trivially simple webcam recorder"
depends=('perl-config-simple')
arch=('any')
license=('GPL')
options=('!strip')
md5sums=('76ef522124a23e3d65fe65de00ae487a')
_vendor_perl=$(perl -V::vendorarch: | sed -e "s![' ]!!g")

package() {
# Manifest
    install -D -m0644 ${startdir}/ubos-manifest.json -t ${pkgdir}/ubos/lib/ubos/manifests/${pkgname}.json

# Place for config files
    mkdir -p ${pkgdir}/etc/vrecorder

# Place for data files
    mkdir -p ${pkgdir}/var/lib/vrecorder

# Code
    install -D -m0755 ${startdir}/{activate,vrecorder}.pl -t ${pkgdir}/ubos/share/${pkgname}/

# Web
    install -D -m0755 ${startdir}/index.php -t ${pkgdir}/ubos/share/${pkgname}/
    install -D -m0644 ${startdir}/style.css -t ${pkgdir}/ubos/share/${pkgname}/

# Templates
    install -D -m0644 ${startdir}/conf.php.tmpl -t ${pkgdir}/ubos/share/$pkgname/
    install -D -m0644 ${startdir}/conf.tmpl     -t ${pkgdir}/ubos/share/$pkgname/
    install -D -m0644 ${startdir}/htaccess.tmpl -t ${pkgdir}/ubos/share/$pkgname/

# systemd service
    install -D -m0644 ${startdir}/vrecorder@.service -t ${pkgdir}/usr/lib/systemd/system/
}

