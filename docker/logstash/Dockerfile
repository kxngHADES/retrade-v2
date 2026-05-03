FROM docker.elastic.co/logstash/logstash:8.12.0
USER root
RUN logstash-plugin install logstash-input-mongodb
RUN mkdir -p /usr/share/logstash/mongodb_state && chown -R logstash:logstash /usr/share/logstash/mongodb_state
USER logstash