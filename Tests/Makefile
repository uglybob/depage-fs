RM = rm -rf

PHONY: test remote-vsftpd clean

test: remote-vsftpd local
	../vendor/bin/phpunit --configuration PHPUnit.xml .

remote-vsftpd:
	$(MAKE) -C docker/remote-vsftpd
	$(MAKE) run -C docker/remote-vsftpd

local: remote-vsftpd
	$(MAKE) -C docker/local
	$(MAKE) run -C docker/local

clean:
	${RM} Report Temp docker/ssl/ca.pem
	$(MAKE) clean -C docker/local
	$(MAKE) clean -C docker/remote-vsftpd
