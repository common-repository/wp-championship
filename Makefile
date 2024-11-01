
#
# Makefile fuer wp-championship
#
all: pot po
pot:
	@xgettext -L PHP -k --keyword=_e  --keyword=__ --from-code=utf-8 --default-domain=wpcs --output=wp-championship.pot *.php;

po:
	@for i in lang/*.po; do \
		echo -n "Merging $$i...";\
		msgmerge -q -U $$i wp-championship.pot;\
		echo "done:";\
	done;\
	echo "Please edit the po files now and start 'make mo'";

mo:
	@for i in lang/*.po;do \
		echo -n "Converting $$i...";\
		msgfmt -o `echo $$i|cut -d"." -f1`.mo $$i; \
		echo "done.";\
	done;
