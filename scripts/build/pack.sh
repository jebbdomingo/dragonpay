
# Create variables
build_dir=$(pwd)
payload_dir=$build_dir/installer/payload

framework_location=remote
framework_branch=v3.0.8

# Clean up
rm -rf installer
rm -f com_dragonpay_installer.zip
rm -f com_dragonpay.zip
rm -f joomlatools-framework.zip

# build dragonpay
phing

# build framework
cd ../../../joomlatools-framework && phing -Dframework.location=$framework_location -Dframework.branch=$framework_branch && mv joomlatools-framework.zip $build_dir/joomlatools-framework.zip
cd $build_dir

# clone installer
git clone --depth 1 --branch master git@github.com:joomlatools/joomlatools-extension-installer.git $build_dir/installer
rm -rf $build_dir/installer/.git
rm -f $build_dir/installer/.gitignore
rm -f $build_dir/installer/README.md
rm -f $build_dir/installer/LICENSE

# create payload
mkdir $payload_dir
cp manifest.json $payload_dir/manifest.json
unzip -q com_dragonpay.zip -d $payload_dir/dragonpay
unzip -q joomlatools-framework.zip -d $payload_dir/framework

# zip it all up
zip -q -r com_dragonpay_installer.zip installer

# clean leftovers
rm -rf installer
rm -f com_dragonpay.zip
rm -f joomlatools-framework.zip