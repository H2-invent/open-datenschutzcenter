#! /bin/sh
cd /var/www/html/
mkdir -p dev_out
for folder in prod dev; do
    mkdir -p "$folder"
    cd "$folder"
    mkdir -p "public"
    if [ "$folder" == "dev"]; then
        for subf in ls ../prod/public; do
            if [ "$subf" != "build" ]; then
                ln -s "../dev_out/$subf" .
            fi
        done
        for subf in ls ../prod/; do
            if [ "$subf" != "public" ] && [ "$subf" != "vendor" ] && [ "$subf" != "node_modules" ] && [ "$subf" != "var" ]; then
                ln -s "../dev_out/$subf" .
            fi
        done
        mkdir -m 777 -p public/build
        npm run dev
    else
        npm run build
        mkdir -m 777 -p public/build
        mkdir -p public/uploads
        chown -R www-data:www-data public/uploads
        chmod -R 775 public/uploads
        mkdir -p public/data
        chown -R www-data:www-data public/data
        chmod -R 775 public/data
    fi
    chmod -R 775 public/build
    mkdir -p var/cache
    chown -R www-data:www-data var
    chmod -R 775 var
    cd ..
done
