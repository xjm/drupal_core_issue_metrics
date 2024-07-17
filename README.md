This repository allows querying Drupal.org metadata for core issues (using the
Drupal 7 site API and issue queue).

Reference: https://www.drupal.org/drupalorg/docs/apis/rest-and-other-apis

# Retrieve data from core issues in active queues.

1. First, fetch all issue data for relevant issues in actively supported
   branches, by running the following from the cloned repository root:

   ```
   php fetch_active_data.php
   ```

   **Warning:** This can take over an hour.

   The output of each request is cached locally with weekly granularity, and
   subsequent requests within the week will retrieve the data from the local cache
   instead of Drupal.org.

2. Then, populate the SQLite database with this data:
   ```
   php populate_database.php
   ```

   The SQLite database is included in the repository for convenience.
   
3. Finally, run any desired queries against the data. For example:
   ```
   php get_untriaged_criticals.php
   ```

# Retrieve core issue contribution data for a user from the previous week

```
php fetch_recent_comments.php xjm
```

Fixed issues are filtered to those issues credited to the individual. Open
issues can be edited by hand to include only creditable contributions.
   
