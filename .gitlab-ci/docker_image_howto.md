# Wgrywanie nowego obrazu Docker-a dla CI

Każda kolejna wersja obrazu Dockera dla CI powinna powodować publikację w katalogu '.gitlab-ci'
aktualnej wersji pliku Dockerfile uzupełnionego o sufiks '_vX', gdzie 'X' to kolejny numer wersji.
Co do zasady kolejne wersje numerowane są rosnącymi liczbami całkowitymi (1, 2, 3, 4, itd.).
Wersja 'latest' powinna odpowiadać najnowszej wersji obrazu (taj z najwyższym sufiksem).

Zakładając, że pracujemy w katalogu zawierającym aktualny Dockerfile:
> sudo docker build -t gitlab.parp.gov.pl:5055/ci/secp:vX .  
> sudo docker push gitlab.parp.gov.pl:5055/ci/secp:vX  
> sudo docker tag gitlab.parp.gov.pl:5055/ci/secp:vX gitlab.parp.gov.pl:5055/ci/secp:latest  
> sudo docker push gitlab.parp.gov.pl:5055/ci/secp:latest  

Pod 'X' należy podstawić numer kolejnej wersji.
Konieczność użycia 'sudo' może być zależna od konfiguracji konkretnego systemu operacyjnego.

# Przebudowa obrazu

Wymuszenie przebudowy obrazu w celu aktualizacji wersji pakietów (np. weszły wersje z poprawkami błędów).
Nie ma konieczności tworzenia nowego pliku kolejną wersją Dockerfile.

Zakładając, że pracujemy w katalogu zawierającym aktualny Dockerfile:
> sudo docker build --no-cache -t gitlab.parp.gov.pl:5055/ci/secp:vX .  
> sudo docker push gitlab.parp.gov.pl:5055/ci/secp:vX  
> sudo docker tag gitlab.parp.gov.pl:5055/ci/secp:vX gitlab.parp.gov.pl:5055/ci/secp:latest  
> sudo docker push gitlab.parp.gov.pl:5055/ci/secp:latest  
