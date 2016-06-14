FROM feedsenvironment_base

# ---------------- #
#   Installation   #
# ---------------- #

RUN apt-get install -y python2.6 python-pip python-cairo python-django \
	python-django-tagging python-twisted python-memcache python-pysqlite2 \
	python-simplejson nginx supervisor

# Install Graphite and StatsD
RUN pip install whisper carbon graphite-web pystatsd

# Install Grafana
RUN mkdir /opt/grafana \
	&& curl -SL https://github.com/jwilder/gofana/releases/download/v0.0.7/gofana-linux-amd64-v0.0.7.tar.gz -o /opt/grafana.tar.gz \
	&& tar -xzf /opt/grafana.tar.gz -C /opt/grafana \
	&& rm /opt/grafana.tar.gz


# ----------------- #
#   Configuration   #
# ----------------- #

# Configure Whisper, Carbon and Graphite-Web
ADD ./graphite/local_settings.py /opt/graphite/webapp/graphite/local_settings.py
ADD ./graphite/carbon.conf /opt/graphite/conf/carbon.conf
ADD ./graphite/storage-schemas.conf /opt/graphite/conf/storage-schemas.conf
ADD ./graphite/storage-aggregation.conf /opt/graphite/conf/storage-aggregation.conf
RUN mkdir -p /opt/graphite/storage/whisper \
	&& touch /opt/graphite/storage/graphite.db /opt/graphite/storage/index \
	&& chown -R www-data /opt/graphite/storage \
	&& chmod 0775 /opt/graphite/storage /opt/graphite/storage/whisper \
	&& chmod 0664 /opt/graphite/storage/graphite.db \
	&& cd /opt/graphite/webapp/graphite \
	&& python manage.py syncdb --noinput


# Configure nginx
ADD graphite-nginx /etc/nginx/sites-available/graphite
RUN echo "daemon off;" >> /etc/nginx/nginx.conf \
	&& ln -s /etc/nginx/sites-available/graphite /etc/nginx/sites-enabled/

# Configure supervisord
ADD ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf


# ---------------- #
#   Expose Ports   #
# ---------------- #

# Grafana
EXPOSE  80

# StatsD UDP port
EXPOSE  8125/udp

# Graphite
EXPOSE  2003/udp

# StatsD Management port
EXPOSE  8126


VOLUME ["/opt/grafana/dashboards/"]

# -------- #
#   Run!   #
# -------- #

CMD ["/usr/bin/supervisord"]
