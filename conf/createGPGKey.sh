#!/usr/bin/env bash
#rm -rf .gnupg
LOG="/var/log/mods_sspks.log"

if [ ! -f ~/.gnupg/gpg.conf ]; then
	mkdir -p -m 0700 ~/.gnupg
	touch ~/.gnupg/gpg.conf
	chmod 600 ~/.gnupg/gpg.conf
	tail -n +4 /opt/share/gnupg/gpg-conf.skel > ~/.gnupg/gpg.conf

	touch ~/.gnupg/{pub,sec}ring.gpg
fi

if [ -d gpg ]; then
	rm -rf gpg
fi
mkdir -p -m 0700 gpg

#generate some entropy
(dd if=/dev/zero of=/dev/null) & pid=$!

#generate the key
echo `date` "Generating Key" >> $LOG
gpg2 --passphrase '' --batch --gen-key ./gpgkey 

#fetch the key id
echo `date` "Retrieving Key" >> $LOG
keyid=$(gpg2 --no-default-keyring --secret-keyring ./gpg/secring.gpg -K | grep -m1 'sec   ' | cut -c 13-20)
echo $keyid > ./gpgkeyid.asc

#export the public key
echo `date` "Exporting Key" >> $LOG
gpg2 --no-default-keyring --keyring ./gpg/pubring.gpg --armor --export $keyid  > ./gpgkey.asc

#kill the entropy generator
kill $pid &> /dev/null