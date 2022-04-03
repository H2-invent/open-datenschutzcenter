#! /bin/sh
# prod

mkdir -m 777 -p public/build
npm run build
mkdir -m 775 -p public/uploads
mkdir -m 775 -p public/data
chmod -R 775 public/build
mkdir -p var/cache
chmod -R 775 var
chown -R www-data:www-data public/uploads
chown -R www-data:www-data public/data
chown -R www-data:www-data var

# dev
mkdir -p dev_out
chown -R www-data:www-data dev_out
chmod -R 775 dev_out
mkdir -p dev
cd dev
mkdir -p public
ln -s ../vendor .
ln -s ../node_modules .
for subf in $(ls ../public); do
    if [ "$subf" != "build" ]; then
        ln -s "../dev_out/$subf" .
    fi
done
for subf in $(ls ../); do
    if [ "$subf" != "dev" ] && [ "$subf" != "dev_out" ] && [ "$subf" != "public" ] && [ "$subf" != "vendor" ] && [ "$subf" != "node_modules" ] && [ "$subf" != "var" ]; then
        ln -s "../dev_out/$subf" .
    fi
done
mkdir -m 777 -p public/build
npm run dev
chmod -R 775 public/build
mkdir -m 775 -p public/uploads
mkdir -m 775 -p public/data
mkdir -p var/cache
chmod -R 775 var
chown -R www-data:www-data public/uploads
chown -R www-data:www-data public/data
chown -R www-data:www-data var

cd ..
