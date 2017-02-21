RM = rm -rf

.PHONY: test test-vsftpd test-proftpd clean

test: test-vsftpd test-proftpd

test-vsftpd:
	$(MAKE) run -C docker/remote-vsftpd
	$(MAKE) run-vsftpd -C docker/local

test-proftpd:
	$(MAKE) run -C docker/remote-proftpd
	$(MAKE) run-proftpd -C docker/local

clean:
	${RM} Report Temp docker/ssl/ca.pem
	$(MAKE) clean -C docker/local
	$(MAKE) clean -C docker/remote-vsftpd
	$(MAKE) clean -C docker/remote-proftpd
