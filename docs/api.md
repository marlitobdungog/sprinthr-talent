# Talent Portal API

Server-to-server API used by SprintHR AI agents. All endpoints live under `/api/v1`.

## Authentication

Every request must send the API key as a bearer token:

```http
Authorization: Bearer <TALENT_API_KEY>
```

- Keys are configured in `.env` as `TALENT_API_KEY`.
- Several keys can be listed separated by commas, so a new key can be added before the old one is removed (key rotation).
- If `TALENT_API_KEY` is empty, every request is rejected.
- Keep keys out of source control and logs.

### Errors

| Status | When |
| --- | --- |
| `401` | Missing, wrong, or empty key. Body: `{"message": "Unauthenticated."}` |

## Endpoints

### Check connectivity

`GET /api/v1/ping`

Use this to confirm your key works.

```bash
curl -H "Authorization: Bearer $TALENT_API_KEY" \
  https://<host>/api/v1/ping
```

Response `200`:

```json
{
  "data": {
    "status": "ok",
    "principal": "service"
  }
}
```

`principal` is `service` for API keys.

## Planned endpoints

These are not implemented yet. The design is in progress.

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/v1/users/{public_id}/resume` | Download an applicant's resume |
| `POST` | `/api/v1/users/{public_id}/extractions` | Submit extracted data as drafts for applicant review |
| `GET` | `/api/v1/users/{public_id}/extractions/{run}` | Check the review status of a submission |

`{public_id}` is the applicant's public ID, a 26-character ULID.

### Submit extracted data

`POST /api/v1/users/{public_id}/extractions`

Use this after an agent has read an applicant's resume and extracted structured data from it. The data is saved as **drafts**. The applicant reviews each draft in their profile and accepts or rejects it. Nothing is added to the profile, or shown to employers, until the applicant accepts.

**Headers**

```http
Authorization: Bearer <TALENT_API_KEY>
Content-Type: application/json
Idempotency-Key: <unique value per submission>
```

- `Idempotency-Key` (required): a value unique to this submission, such as a UUID. If the same key is sent again, for example after a timeout, the API returns the original result instead of creating a second submission. Use a new key for each new resume extraction.

**Request body**

At least one of `skills`, `experiences`, or `educations` must be present.

```json
{
  "source": {
    "agent": "resume-parser",
    "resume_id": "res_208",
    "extracted_at": "2026-11-10T09:30:00Z"
  },
  "skills": [
    { "name": "Laravel", "experience_years": 6 },
    { "name": "PostgreSQL" }
  ],
  "experiences": [
    {
      "external_id": "exp-1",
      "title": "Senior Backend Developer",
      "company": "Acme Corp",
      "started_on": "2021-03-01",
      "ended_on": "2024-08-31",
      "current_role": false,
      "description": "Led API platform migration."
    }
  ],
  "educations": [
    {
      "institution": "University of the Philippines",
      "qualification": "BS Computer Science",
      "started_year": 2012,
      "ended_year": 2016
    }
  ]
}
```

**Fields**

| Field | Required | Notes |
| --- | --- | --- |
| `source.agent` | Yes | Name of the agent that produced the extraction |
| `source.resume_id` | No | The resume the data came from |
| `source.extracted_at` | No | When the extraction ran (ISO 8601) |
| `skills[].name` | Yes | 2 to 80 characters. Duplicates of existing skills are flagged for review |
| `skills[].experience_years` | No | Whole number, 0 to 60 |
| `experiences[].external_id` | No | Your own ID for the item. Sending the same ID later updates the item instead of duplicating it |
| `experiences[].title`, `company` | Yes | Up to 140 characters each |
| `experiences[].started_on`, `ended_on` | No | `YYYY-MM-DD`. `ended_on` must not be before `started_on` |
| `experiences[].current_role` | No | If `true`, `ended_on` is ignored |
| `experiences[].description` | No | Up to 3000 characters |
| `educations[].institution`, `qualification` | Yes | Up to 140 characters each |
| `educations[].started_year`, `ended_year` | No | Four-digit years |

**Response `202 Accepted`**

```http
Location: /api/v1/users/01HZX3KQ8M7W2N5R9T4VYB6C0D/extractions/run_77ac
```

```json
{
  "data": {
    "id": "run_77ac",
    "status": "pending_review",
    "counts": { "skills": 2, "experiences": 1, "educations": 1 },
    "created_at": "2026-11-10T09:30:02Z"
  }
}
```

The response doesn't include the applicant's data.

**Check status**

`GET /api/v1/users/{public_id}/extractions/{run}`

Status values:

| Status | Meaning |
| --- | --- |
| `pending_review` | Waiting for the applicant to review |
| `partially_accepted` | Some drafts accepted, some rejected or still pending |
| `accepted` | All drafts reviewed and accepted |
| `rejected` | All drafts rejected |

**Errors**

| Status | When |
| --- | --- |
| `401` | Missing or wrong key |
| `404` | No applicant with that `public_id` |
| `409` | `Idempotency-Key` was already used with a different body |
| `422` | Validation failed. The body lists each field that's wrong, for example `{"message": "...", "errors": {"experiences.0.title": ["The title field is required."]}}` |
| `413` | Request body is too large (over 256 KB) |

**Example**

```bash
curl -X POST "https://<host>/api/v1/users/01HZX3KQ8M7W2N5R9T4VYB6C0D/extractions" \
  -H "Authorization: Bearer $TALENT_API_KEY" \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: 4e1b7a52-resume-run-001" \
  -d @extraction.json
```

## Rate limits

Not enforced yet. Planned limits, applied per API key:

| Limit | Planned value | Applies to |
| --- | --- | --- |
| General requests | 120 per minute | All endpoints |
| Extraction submissions | 30 per minute | `POST .../extractions` |
| Resume downloads | 60 per minute | `GET .../resume` |
| Per applicant | 10 extraction submissions per hour | Each `{public_id}` |

When a limit is reached, the API returns `429 Too Many Requests` with a `Retry-After` header (seconds to wait). Agents should wait and retry.

Other planned safeguards:
- Request body limit of 256 KB.
- Maximum of 100 items per list (skills, experiences, educations) in one submission.

The limits will be set in `config/talent.php` so they can be changed without code changes.
