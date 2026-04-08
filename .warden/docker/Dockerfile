FROM selenium/node-chrome:4
USER root

COPY .warden/certs/ca.cert.pem /etc/certs/ca.cert.pem
RUN apt-get update -qqy \
  && apt-get -qqy install \
    libnss3-tools \
  && rm -rf /var/lib/apt/lists/* /var/cache/apt/*

USER seluser
WORKDIR /home/seluser

RUN mkdir -p -m 755 .pki/nssdb \
    && echo "" > .pki/nssdb/password.txt \
    && certutil -d sql:.pki/nssdb -N -f .pki/nssdb/password.txt \
    && certutil -d sql:.pki/nssdb -A -t "C,," -n warden-ca -i /etc/certs/ca.cert.pem

