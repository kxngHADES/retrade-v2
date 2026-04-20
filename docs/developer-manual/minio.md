# Minio


## Set container to public
```bash
# create alias
docker compose exec minio mc alias set myminio http://localhost:9000 USERNAME PASSWORD

# list existing buckets
docker compose exec minio mc ls myminio

# set policy
deocker compose exec minio mc anonymous set public myminio/bucket_name

# verify policy is set
docker compose exec minio mc anonymous get myminio/bucket_name
```



## If no bucket
```bash
# Create the bucket
docker compose exec minio mc mb myminio/bucket_name

# Set it to public
docker compose exec minio mc anonymous set public myminio/bucket_name

# Verify
docker compose exec minio mc anonymous get myminio/bucket_name
```