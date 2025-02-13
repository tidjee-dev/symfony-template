FROM phpmyadmin/phpmyadmin

# Update and Install dependencies | Mise à jour et installation des dependances
RUN apt-get update && apt-get install -y unzip lsd

# Create "themes" directory | Creation du dossier "themes"
RUN mkdir -p /var/www/html/themes

# Add of the `Blueberry` theme | Ajout du theme `Blueberry`
RUN curl -L https://files.phpmyadmin.net/themes/blueberry/1.1.0/blueberry-1.1.0.zip > /tmp/blueberry-1.1.0.zip && \
  unzip /tmp/blueberry-1.1.0.zip -d /tmp/blueberry-1.1.0/ && rm /tmp/blueberry-1.1.0.zip && \
  mv /tmp/blueberry-1.1.0/blueberry /var/www/html/themes/

# Create useful aliases | Creation d'alias utiles
RUN echo "alias cls='clear'" >> ~/.bashrc \
  echo "alias ls='lsd -al --group-dirs first'" >> ~/.bashrc

# Set working directory | Definition du repertoire de travail
WORKDIR /var/www/html
