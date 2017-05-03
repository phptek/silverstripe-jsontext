##2.0

* MINOR: Tweaked README and travis config to suit SS4+PHP7 [view commit](https://github.com/phptek/silverstripe-jsontext/commit/fea02f918e819a041f1eaeb5b0cec7b4c4344c4d)
* FIX: Adapted existing tests to SS4 and all is green. [view commit](https://github.com/phptek/silverstripe-jsontext/commit/4d7a5d255b6742cac80696e1d88a920553e33ec9)
* API: Refactored namespaces to include maintainer GitHub acc name. - Altered namespaces to consumed classes to suit SS4 [view commit](https://github.com/phptek/silverstripe-jsontext/commit/764d9c975df41f33c04373ed140662d51a87b6ae)
* MINOR: Updated docs to suit instructions for SS4 & SS3 [view commit](https://github.com/phptek/silverstripe-jsontext/commit/ff5f2ca129ea8594e9774b4cb710ad9e013fa14e)

##1.0

* NEW: Fixes #7 - Added always-on DataExtension for no-db-field getCMSFelds() fields (!!). - API Made JSONtext::toArray() more flexible to deal with internal getValue() if no $value passed. - Updated docs. - Also resolves default (null) field value issue on dev/build. [view commit](https://github.com/phptek/silverstripe-jsontext/commit/b5c4207a7aa5cbc69e83c9152b0086e7789d0220)
* MINOR: Updated README with TODO items. [view commit](https://github.com/phptek/silverstripe-jsontext/commit/64942cb03fbbd8ce6a56f3a573ab4b2665431990)
* MINOR: Tweaked Composer keywords. [view commit](https://github.com/phptek/silverstripe-jsontext/commit/a238bbbe725e789c03edb000b75daadf33b7f1d1)
* NEW: Compliments 39204a983 via displaying JSON data in the selected CMS input fields. [view commit](https://github.com/phptek/silverstripe-jsontext/commit/0d7657cd17f3ab3f2b2257ad3a05aa0ebcd9d49a)
* NEW: Allow CMS feld-data to be converted to JSON and stored to a specific DB field. [view commit](https://github.com/phptek/silverstripe-jsontext/commit/32cc9c8b1b61fd9b06c8368907031aef40d522c5)
* Removed tests from gitattributes. These are useful to have. Added dedicated Exceptions for specific scenarios. Added more tests. [view commit](https://github.com/phptek/silverstripe-jsontext/commit/39204a9839cfc4df407bd47de46fb8fa7bd0970d)
* MINOR: Improved range of accepted JSONPath expressions in JSONText::isValidExpression() including test cases. [view commit](https://github.com/phptek/silverstripe-jsontext/commit/25699d5c8e53e7c90ab2ad459e92255d3feac08d)
* MINOR: Added completed changelog up to 0.7 [view commit](https://github.com/phptek/silverstripe-jsontext/commit/c121e31670db3af048e4acec447ea55771165b68)

##0.7  

 * MINOR: Docs [view commit](http://github.com/phptek/silverstripe-jsontext/commit/09c137842e8cfe35efb032287eb240effd998890)
 * MINOR: inline docs. Removed redundant fixture ref [view commit](http://github.com/phptek/silverstripe-jsontext/commit/dd16546e84ee4d04118adde4ab0c03337724e304)
 * API: Used DI for SS to use non-namespaced field scaffolding in $db static. MINOR: Update docs and comments FIX: Removed debugging test and corrected copy/pasted test issue [view commit](http://github.com/phptek/silverstripe-jsontext/commit/ad296ee9b4ee86b02e7552aa9ac1f87fceaed94a)
 * API: Moved $allowed_operators config static to JSONBackend. Updated comments [view commit](http://github.com/phptek/silverstripe-jsontext/commit/5844b862f58251414892407ea4765df763042ac0)

##0.6 

 * MINOR: Missing scope on abstract method [view commit](http://github.com/phptek/silverstripe-jsontext/commit/bae9c50035c9b05443b3c42821fa83f5fdf1b3f9)
 * MINOR: Typo in README [view commit](http://github.com/phptek/silverstripe-jsontext/commit/981da1dc1e27605b2bb1ad28214dbcee000d64e4)
 * Modified behaviour of `setValue()`. Mostly refuses to accept invalid JSON in the first place apart from zero-length strings which SS needs to clear DBField types with [view commit](http://github.com/phptek/silverstripe-jsontext/commit/6ca8f1b26e9cd317ddc275827578a1a6992a7a8d)
 * Simplified some logic. Removed repetition. [view commit](http://github.com/phptek/silverstripe-jsontext/commit/2afc8624c22466470a539b4fb0e173d7fa31ec69)
 * Fixed non-namespaced calls to HiddenField. - Added JSON check to `setValue()` when called with JSONPath expression. - Added tests for `JSONText::isJSON()` [view commit](http://github.com/phptek/silverstripe-jsontext/commit/c1f2b6081c8546a6600e263d7628d9e26de78998) 

##0.5

 * FIX: Really fixes Travis failures for PHP v5.4 and SS v3.1 [view commit](http://github.com/phptek/silverstripe-jsontext/commit/5cb32aa3e11bd5de3bd54298a517283c35e2c665)
 * FIX: Fixes Travis build failure (Weird PHP 5.4 root-namespace issue) [view commit](http://github.com/phptek/silverstripe-jsontext/commit/eedf4e73cf07a1139657a997edebf1d111fa6898)
 * FIX: Various issues found by Scrutinizer [view commit](http://github.com/phptek/silverstripe-jsontext/commit/cf178da87f58aad0411aa3bc63e1b82daad02654)
 * Merge branch 'issue/5' [view commit](http://github.com/phptek/silverstripe-jsontext/commit/9eb09df344e4500f1a435d469bc4b0aaa42c4237)
 * NEW: Fixes #5 - Added integration tests. - Removed redundant JSON fixtures. - Updated docs accordingly [view commit](http://github.com/phptek/silverstripe-jsontext/commit/fe140df88cf85ff8d31262d3459df8445b668a40)
 * More docs [view commit](http://github.com/phptek/silverstripe-jsontext/commit/f2c532a1fa97de49645f4c5e3e18f941bf218bd0)
 * MINOR: More docs [view commit](http://github.com/phptek/silverstripe-jsontext/commit/a18c8d4abf7a2826ba26682368997551641b109a)
 * MINOR: Updated docs with or einfo on JSONPath expressions [view commit](http://github.com/phptek/silverstripe-jsontext/commit/a803ba0ce1088a0d22e2ec97753c866bbea5f0f9)
 * MINOR: More Scrutinizer fixes [view commit](http://github.com/phptek/silverstripe-jsontext/commit/75b5761f42b98362c2e226ad66a9a396f8149eb1)
 * FIX: Minor issues as reported by Scrutinizer [view commit](http://github.com/phptek/silverstripe-jsontext/commit/1f0d306d1e60db2bb341d3f04b0b8b2e19ea4b49)

##0.4

 * Removed stupid phpunit.xml...mutter. [view commit](http://github.com/phptek/silverstripe-jsontext/commit/8f56bda53317daedd3dd937ab85562491963be4d)
 * Added setValue[] tests - Reorganised project directory structure - Added default YML config - Added phpunit XML for ease of running tests - Updated docs [view commit](http://github.com/phptek/silverstripe-jsontext/commit/af643a190d67eeea2c20fd0315866ea346e05b5b)

##0.3

 * Merge branch 'issue/3' [view commit](http://github.com/phptek/silverstripe-jsontext/commit/7b46e521da1bf32462d67358d9ad232b49eac9c1)
 * NEW: Fixes #3 Expose JSONPath query API. NEW: Modified setValue[] to selectively modify JSON nodes. MINOR: Travis config. [view commit](http://github.com/phptek/silverstripe-jsontext/commit/f496af61074dc3bee85ed7bc42fdecc20742f5e7)

##0.2

 * Updated docs [view commit](http://github.com/phptek/silverstripe-jsontext/commit/301d7506957dab3ffbacd2c320c9556b0929bef9)
 * Merge branch 'master-experimental' [view commit](http://github.com/phptek/silverstripe-jsontext/commit/627b5c6078bdc4984343e9d9685b71f0ea781506)
 * [view commit](http://github.com/phptek/silverstripe-jsontext/commit/c0107234021f9d8d20cdf544ceabc5f38f05c36e) - Used peekmo/jsonpath for heavy lifting - Added "silverstripe" as valid return type - Added PostgresJSONBackend, JSONBackend is abstract - Refactored and added tests [view commit]

##0.1

 * MINOR: Travis branch exclusion and README typo [view commit](http://github.com/phptek/silverstripe-jsontext/commit/9d18b473e95480cd0b86db302cb542d2a75934ab)
 * MINOR: Update docs [view commit](http://github.com/phptek/silverstripe-jsontext/commit/053ce8f804577dfee6fda60d54467dc3da64dbbf)
 * MINOR: Paths to markdown docs [view commit](http://github.com/phptek/silverstripe-jsontext/commit/5934b1119c330f77eca6bc292fd288da21fae83c)
 * MINOR: Updated README and separated sections into own markdown files in own docs dir [view commit](http://github.com/phptek/silverstripe-jsontext/commit/3924fac23101b1f929d6bf8628c6e959b3408623)
 * MINOR: README formatting [view commit](http://github.com/phptek/silverstripe-jsontext/commit/4d9ca8d5089587e78d2643e1fe4124525a33518a)
 * MINOR: Added Scrutinizer shield to README [view commit](http://github.com/phptek/silverstripe-jsontext/commit/dc798636975810d6fda647c204ed4cce8b0b634c)
 * Minor Travis and README tweaks [view commit](http://github.com/phptek/silverstripe-jsontext/commit/1eec68a5021a88e3b090974695fb56e4abe18fb1)
 * Added missing files required to satisfy Module Standard 1.0 Update README accordingly [view commit](http://github.com/phptek/silverstripe-jsontext/commit/9a508f8e2b1078ff4f8e27e992546fe5bacb7679)
 * MINOR: Phpdoc [view commit](http://github.com/phptek/silverstripe-jsontext/commit/dacd1064607bf3a1837f67be690a23e3f959dcc8)
 * Uncommented test for duplicate keys in sourfce data. Minor code-comments [view commit](http://github.com/phptek/silverstripe-jsontext/commit/67ffab39b7199b4b77db0fb12157d061e46acaf2)
 * Changed posr LICENSE file path [view commit](http://github.com/phptek/silverstripe-jsontext/commit/471861cc4c9708464822670d7eb38497eeafc114)
 * Updated README with posr License icon and renamed LICENSE file. Modified tests to pass on PHP <5.6 [view commit](http://github.com/phptek/silverstripe-jsontext/commit/2c76e39bf6b243894e5a6f997a5528d50c0a938a)
 * Added more (failing - commented) tests & logic Updated Travis config [view commit](http://github.com/phptek/silverstripe-jsontext/commit/01120a27617fea823444fe83e4be9c44b82912eb)
 * Fixed faulty composer.json [view commit](http://github.com/phptek/silverstripe-jsontext/commit/4cc8d7d147a898a73e18a42f25ddf182066289ca)
 * Added Postgres '#>' patch matcher operator and updated README [view commit](http://github.com/phptek/silverstripe-jsontext/commit/83f346c1d15aa95808b6546b03a383247e1e3e62)
 * Reorganised test fixtures. [view commit](http://github.com/phptek/silverstripe-jsontext/commit/72fad78ad27fe5060f3c7fb1d31e5b3eee49403f)
 * Added namespaces. [view commit](http://github.com/phptek/silverstripe-jsontext/commit/8777316d0974f0d456ac48223b752461308c2ea1)
 * MINOR: Added Travis build status icon to README [view commit](http://github.com/phptek/silverstripe-jsontext/commit/0542043d765ff7455ae90ed37c93ab8af12fdcf2)
 * Added travis config [view commit](http://github.com/phptek/silverstripe-jsontext/commit/1dda5a6e24193d2047e595f9045f232e4b4f7d21)
 * Added basic Scrutinizer config [view commit](http://github.com/phptek/silverstripe-jsontext/commit/d0d98a56044fa946ee258e2c776efaa8c18fbbcb)
 * Minor formatting [view commit](http://github.com/phptek/silverstripe-jsontext/commit/cb9664e4f49d206101265a5d95c714e59dd9daa5)
 * More tests. - Renamed extract[] to query[]. - Updated README - Split INT and STRING queries into '->' and '->>' operators [view commit](http://github.com/phptek/silverstripe-jsontext/commit/c30f08acb0882d2e4973b66dfefa078d24e527c7)
 * W.I.P Deeper JSON structures new accepted [view commit](http://github.com/phptek/silverstripe-jsontext/commit/b421bbf23d7fc69ad68b2e21f4474eb4c783899e)
 * W.I.P - Adding ability to query by value. Results should be an array to   account for multiple top-level keys containing >1 matches [view commit](http://github.com/phptek/silverstripe-jsontext/commit/749c7cc3ff6ba59006b63cdd26dc6b72af2a820f)
 * Added dedicated backend class designed to soley deal with DB-specific differences in JSON querying. Updated tests, all pass [view commit](http://github.com/phptek/silverstripe-jsontext/commit/9925d05e7c1290b5766177d456cc7343521d24c0)
 * W.I.P converted from straight array to RecursiveIteratorIterator. Updated tests [view commit](http://github.com/phptek/silverstripe-jsontext/commit/05a1e4f4922314a6d9dbdbd20e94bef08b76b5e1) 
 * Amended logic, fixed+improved tests [view commit](http://github.com/phptek/silverstripe-jsontext/commit/70c2cf2f3cc6331d86d56886b02a44c4562768f0)
 * Added tests [view commit](http://github.com/phptek/silverstripe-jsontext/commit/9aeddc94f989e6b769ed9231dbeba460bb12e8a1)
 * Renamed markdown files [view commit](http://github.com/phptek/silverstripe-jsontext/commit/fa838f23f0c65964f821f2f53f461d06a52a3f80)
 * Initial commit [view commit](http://github.com/phptek/silverstripe-jsontext/commit/c6534fe05ff4a0d2e74937d53dc0f7cf5daac94e)
