# Deploy ke Dokploy

Pipeline: `push main` → CI: test → build & push `ghcr.io/<owner>/<repo>:latest` → trigger Dokploy via API.

## 1. Buat PAT GitHub (read:packages)

GitHub → Settings → Developer settings → Personal access tokens → *classic* dengan scope `read:packages` (dipakai Dokploy menarik image GHCR; bisa dipakai juga untuk registry CI).

## 2. Connect Registry GHCR di Dokploy

Settings → **Registries** → Tambah Registry:
- Type: **GitHub Container Registry**
- Username: username GitHub
- Password: PAT read:packages

## 3. Buat Service (Application)

Projects → buat project → **Application** (source: **Registry**) → pilih registry GHCR:
- Image: `ghcr.io/<owner>/<repo>:latest`

## 4. Volume (SQLite persist)

Volumes → tambah:
- Volume mount: `/var/www/html/database` (bertipe volume named/absolute path; SQLite tersimpan di sini, termasuk flag `.seeded`).

## 5. Environment Variables

Tambahkan (yang tak ada akan di-generate entrypoint):
| Key | Nilai |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://domain-publik-anda` |
| `SEED_DB` | `true` (isi dummy sekali; flag `.seeded` mencegah re-seed) |

`APP_KEY` dibuat otomatis saat container pertama jalan.

## 6. Domain & SSL

Domains tab → tambah domain → enable HTTPS (Dokploy generate Let's Encrypt + Traefik).

## 7. Secret GitHub Actions

Repository → Settings → Secrets → Actions:
- `DOKPLOY_URL` — host Dokploy tanpa https:// (mis. `dokploy.example.com`)
- `DOKPLOY_API_TOKEN` — token API Dokploy (Profile → Tokens)
- `DOKPLOY_APPLICATION_ID` — didapat via:
  ```bash
  curl -X GET "https://<dokploy>/api/project.all" -H "x-api-key: <token>"
  ```

## Trigger manual

```bash
curl -X POST "https://<dokploy>/api/application.deploy" \
  -H "content-type: application/json" \
  -H "x-api-key: <token>" \
  -d '{"applicationId": "<id>"}'
```

## Reset data

```bash
# dalam container
php artisan migrate:fresh --seed --force
touch /var/www/html/database/.seeded
```