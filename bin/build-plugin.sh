#!/bin/bash
# Build script for Eighty/20 Results - E20R MailChimp Integration for Membership Plugins
#
short_name="e20r-pmpro-email-confirmation"
server="eighty20results.com"
include=(css js inc lib/10quality/license-keys-php-client lib/select2/select2 class-${short_name}.php README.txt)
exclude=(*.yml *.phar composer.* vendor)
sed=/usr/bin/sed
build=(plugin-updates/vendor/*.php)
plugin_path="${short_name}"
version=$(egrep "^Version:" ../class-${short_name}.php | sed 's/[[:alpha:]|(|[:space:]|\:]//g' | awk -F- '{printf "%s", $1}')
metadata="../metadata.json"
src_path="../"
dst_path="../build/${plugin_path}"
kit_path="../build/kits"
kit_name="${kit_path}/${short_name}-${version}"
debug_name="${kit_path}-debug/${short_name}-debug-${version}"
debug_path="../build/${plugin_path}-debug"

echo "Building kit for version ${version}"

mkdir -p ${kit_path}
mkdir -p ${kit_path}-debug
mkdir -p ${dst_path}
mkdir -p ${debug_path}

if [[ -f  ${kit_name} ]]
then
    echo "Kit is already present. Cleaning up"
    rm -rf ${dst_path}
    rm -rf ${debug_path}
    rm -f ${kit_name}
    rm -f ${debug_name}
fi

for p in ${include[@]}; do

    if [[ 'lib/select2/select2' == ${p} ]]; then
        cp -R ${src_path}${p} ${dst_path}/includes/
        cp -R ${src_path}${p} ${debug_path}/includes/
    else
        cp -R ${src_path}${p} ${dst_path}
        cp -R ${src_path}${p} ${debug_path}
    fi
done

echo "Stripping Debug data from sources"
find ${dst_path} -type d -name 'plugin-updates' -prune -o -type f -name '*.php' | xargs ${sed} -i '' "/.*->log\(.*\);$/d"

for e in ${exclude[@]}; do
    find ${dst_path} -type d -iname ${e} -exec rm -rf {} \;
    find ${debug_path} -type d -iname ${e} -exec rm -rf {} \;
done

#for b in ${build[@]}; do
#    cp ${src_path}${b} ${dst_path}/plugin-updates/vendor/
#    cp ${src_path}${b} ${debug_path}/plugin-updates/vendor/
#done


cd ${dst_path}/..
zip -r ${kit_name}.zip ${plugin_path}
cd ${debug_path}/..
zip -r ${debug_name}.zip ${plugin_path}-debug
cd ${dst_path}/..
ssh ${server} "cd ./${server}/protected-content/ ; mkdir -p \"${short_name}\""
scp ${kit_name}.zip ${server}:./${server}/protected-content/${short_name}/
scp ${kit_name}-debug.zip ${server}:./${server}/protected-content/${short_name}/
scp ${metadata} ${server}:./${server}/protected-content/${short_name}/
ssh ${server} "cd ./${server}/protected-content/ ; ln -sf \"${short_name}\"/\"${short_name}\"-\"${version}\".zip \"${short_name}\".zip"
rm -rf ${dst_path}
rm -rf ${debug_path}
