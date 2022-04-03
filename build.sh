#! /bin/sh

cd /var/www/html/
chown -R docker:docker .

# prod

mkdir -m 777 -p public/build
su docker -c "npm run build"
mkdir -m 775 -p public/uploads
mkdir -m 775 -p public/data
chmod -R 775 public/build
mkdir -p var/cache
chmod -R 775 var
chown -R www-data:www-data public/uploads
chown -R www-data:www-data public/data
chown -R www-data:www-data var

# dev
mkdir -m 555 -p dev_out
chown -R docker:docker dev_out
mkdir -m 775 -p dev
cd dev
mkdir -m 755 -p public
for subf in $(ls -A ../public); do
    if [ "$subf" != "build" ] && [ "$subf" != "data" ] && [ "$subf" != "uploads" ] && [ "$subf" != "index.php" ]; then
        ln -s "../../dev_out/public/$subf" ./public
    fi
done
cp -a "../public/index.php" ./public
for subf in $(ls -A ../); do
    if [ "$subf" != "dev" ] && [ "$subf" != "dev_out" ] && [ "$subf" != "public" ]; then
        if [ -e "../dev_out/$subf" ] && [ "$subf" != "vendor" ] && [ "$subf" != "node_modules" ] && [ "$subf" != "var" ]; then
            ln -s "../dev_out/$subf" .
        else
            ln -s "../$subf" .
        fi
    fi
done
mkdir -m 777 -p public/build
su docker -c "npm run dev"
chmod -R 775 public/build
mkdir -m 775 -p public/uploads
mkdir -m 775 -p public/data
mkdir -p var/cache
chmod -R 775 var
chown -R docker:docker .
chown -R www-data:www-data public/uploads
chown -R www-data:www-data public/data
chown -R www-data:www-data var

cd ..
