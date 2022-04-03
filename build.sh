#! /bin/sh
# prod

npm run build
mkdir -m 777 -p public/build
mkdir -p public/uploads
chown -R www-data:www-data public/uploads
chmod -R 775 public/uploads
mkdir -p public/data
chown -R www-data:www-data public/data
chmod -R 775 public/data
chmod -R 775 public/build
mkdir -p var/cache
chown -R www-data:www-data var
chmod -R 775 var

# dev
mkdir -p dev_out
chown -R www-data:www-data dev_out
chmod -R 775 dev_out
mkdir -p dev
cd dev
mkdir -p public
ln -s ../vendor .
ln -s ../node_modules .
for subf in ls ../public; do
    if [ "$subf" != "build" ]; then
        ln -s "../dev_out/$subf" .
    fi
done
for subf in ls ../; do
    if [ "$subf" != "dev" ] && [ "$subf" != "dev_out" ] && [ "$subf" != "public" ] && [ "$subf" != "vendor" ] && [ "$subf" != "node_modules" ] && [ "$subf" != "var" ]; then
        ln -s "../dev_out/$subf" .
    fi
done
mkdir -m 777 -p public/build
npm run dev
mkdir -p public/uploads
mkdir -p public/data
mkdir -p var/cache

cd ..
chown -R www-data:www-data dev
chmod -R 775 dev
