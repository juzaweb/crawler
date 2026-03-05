### v2.0.0 
* Refactor(crawler): remove category field from page-item component
* feat: add XML export/import for crawler sources with modal UI
* feat: add export/import for crawler sources and pages
* feat: Update CrawlerServiceProvider to schedule page crawling every fifteen minutes
* feat(post-job): update crawler log status and error when job fails
* feat: add repost bulk action to crawler logs
* Add Inactive bulk action to Crawler Sources DataTable
* feat: add activate and deactivate bulk actions to CrawlerSourcesDataTable
* feat(Crawler): add filterable fields to CrawlerLog and CrawlerSource models
* fix(CrawlPageCommand): optimize query by selecting all columns directly
* Add Feature tests for Crawler admin routes and fix associated bugs
* refactor(CrawlerServiceProvider): update scheduling frequency for crawl commands
* Delete tests/Feature/ScheduleTest.php
* Register schedule for crawl:pages and crawl:links
* docs(crawler): update README example to create or update product based on existing post from crawler log.
* feat: Refactor content posting logic to use PostJob for improved job handling and error management
* fix(crawler-sources): Add rel attributes to links for improved security
* Delete tests/Feature/CrawlerLogControllerTest.php
* feat: add bulk retry action for crawler logs
* Refactor Crawler Log index view to use standard DataTable filters
* Show error modal when clicking on failed log status
* docs: improve README with detailed usage and configuration instructions
* feat(crawler): use page's locale instead of hardcoded 'en' when creating new crawled links
* style(crawler): improve pages count link appearance and format edit action in data tables
* fix(HtmlElement): improve image URL handling by prioritizing data attributes
* feat(HtmlElement): add image processing for figure elements to handle lazy loading and proxy URLs
* feat: add cr_is_internal_url function to enhance internal URL checking in helpers
* fix: trim whitespace from HTML and text outputs in various elements
* fix(crawler): skip components without 'element' key in CrawlerSource
* Improve CrawlerLogsDataTable with formatted status, post column, and clickable URL
* refactor(crawler): Remove unused imports and AJAX data filtering from CrawlerLogsDataTable.
* Add Pages column to CrawlerSources DataTable and remove Pages action from dropdown
* Delete tests/Feature/CrawlerLogsDataTableTest.php
* fix: add error field to CrawlerLog for improved logging of post completion status
* Add filters for Source, Page, and Status to Crawler Log management
* fix(crawler): clear error data and unset error key from content JSON upon successful link crawl
* fix: remove PostJob dispatch from ContentToPostCommand and improve error handling
* feat(crawler): enhance crawler log handling with improved status management and error handling
* Add filters for Source, Page, and Status to Crawler Log management
* feat(crawler): Implement content posting workflow with `CRAWLED` and `POSTING` statuses, `PostJob`, `ContentToPostCommand`, and enhanced UI for crawler forms.
* Add CrawlerLog management in Admin
* feat: add removes repeater to crawler source form
* fix(crawler): handle empty hrefs in HtmlElement and track successful crawls in CrawlLinkCommand
* feat(crawler): refactor crawler log handling and enhance link crawling process with new status management and component mapping
* Fix crawler-sources/{sourceId}/pages and add to Datatable
* Fix crawler-sources/{sourceId}/pages: add support for categories and locales
* feat(crawler): add category selection and enhance form layout for crawler pages
* Add locale select field to Crawler Pages form
* Add locale field to Crawler Pages form
* feat(crawler): add CrawlLinkCommand for link crawling and content management
* feat(crawler): add default 'en' locale when creating new crawled page entries
* feat(crawler): enhance data handling with new methods, update timestamps, and add autoloading for helper functions
* feat(crawler): implement new crawling functionality with PoolCrawler, Crawler, and command for page crawling
* feat(crawler): update data type handling and improve request validation in CrawlerSourceRequest
* refactor(crawler): update module namespaces from JuzawebModulesCrawler to Juzaweb\Modules\Crawler
* feat(crawler): add ArrayStringElement, StringElement, HtmlElement, and BaseElement classes for enhanced HTML parsing
* Add .idea and .phpunit.result.cache to .gitignore
* Add url_with_page to crawler_pages repeater
* Add crawler_pages repeater to crawler-sources form
* feat(crawler): Add data type registration and component retrieval for crawler sources
* feat(crawler): introduce CrawlerRepository for managing data types and integrate into CrawlerSource model and service provider.
* feat(crawler): add component item view and update form layout
* fix(crawler): update admin URL for crawler sources to use kebab-case
* feat(crawler): rename 'domain' to 'name' in crawler_sources table and update related files
* feat(crawler): register crawler and sources menus, update layout extends
* refactor(crawler): Remove `crawler_page_category` table migration and move `juzaweb/core` to dev dependencies.
* refactor(crawler): remove explicit database connection properties from migration files
* fix: Correct CI workflow branches, asset compilation paths, and service provider configuration paths.
* refactor(crawler): simplify migration logic by removing explicit connection calls and `hasTable` checks, and add `crawler_page_category` table drop.
* Remove websiteId parameter and column from routes and database
* feat(crawler): initialize package infrastructure and refactor module namespace from Admin to Core.
* tada: Begin v2.x
* :truck: Move auto crawl
* :bug: Fix import by template
* :+1: Translate HTML
* :+1: Add source_content_id
* :+1: Move crawler entity
* :+1: Horizon support
* :+1: Hidden default templates

