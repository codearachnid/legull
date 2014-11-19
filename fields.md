#Plugin field list
The following list of fields signify the amount and type of data this plugin tracks in order to generate the legal documents.


###Ownership

- [x] `siteurl` (text|readonly) Site url
- [x] `sitename` (text) Site name
- [x] `owner_name` (text) site owner name
- [x] `owner_email` (text) site owner contact email
- [x] `owner_locality` (text) Set the legal physical locality for the site. (i.e. City, State/Provence)
- [x] `has_california` (true/false)
- [x] `entity_type` (select) Is the owner an individual person, or business entity?

###Data & Privacy

- [x] `privacy_email` contact email for privacy matters
- [x] `privacy_address` contact address for privacy matters
- [x] `has_cookies` (true/false) Will this site use cookies beyond advertising tools?
- [x] `has_info_track` (true/false) Will visitors be tracked when surfing the site?
- [x] `has_personalization` (true/false) Will visitors be able to personalize their expereience when surfing the site?
- [x] `has_anonymous` (true/false) Will visitors be able to surf the site anonymously?
- [x] `has_purchased_data` (true/false) Does this site purchase user data?
- [x] `has_data_buyer` (true/false) Does this site sell or rent user data?
- [ ] `has_collectdata` (true/false) Will this site collect any data from its users?
- [ ] `has_sharedata` (true/false) ask if has_collectdata = true : Will any of the user data (individual data or aggregate data) be shared outside of the site owner itself?
- [ ] `has_sharedata_aggregate` (true/false) ask if has_sharedata = true : Will all user data be shared only in grouped form, so that individual users are not identified and individual user data is not shared?
- [ ] `has_sharedata_helpers` (true/false) ask if has_sharedata = true : Will any user data be shared with those who help the site owner operate and manage the site?
- [ ] `has_sharedata_ads` (true/false) ask if has_sharedata = true : Will any user data be shared with advertisers or marketing partners?
- [ ] `has_sharedata_unlimited` (true/false) ask if has_sharedata = true : Will any user data be shared with others other than those who help operate and manage the site, and other than advertisers or marketing partners?
- [ ] `has_no13` (true/false) Will this site allow users or visitors under the age of 13?

###Ecommerce

- [ ] `has_ecommerce` (true/false) : Will this site sell goods or services to customers?  ADD-ON TRIGGER
- [ ] `has_ecommerce_digital` (true/false) ask if has_ecommerce = true : Will the goods or services include digital products for download?
- [ ] `has_ecommerce_physical` (true/false) ask if has_ecommerce = true : Will this site sell customers physical products (objects mailed or shipped to them)?
- [ ] `has_subscription` (true/false) : Will this site offer a subscription service (monthly or yearly, etc.) use or access to your product or service)?
- [ ] `has_3ps` site has third party sellers : Will this site sell products that you do not store and ship?
- [ ] `has_returns` (true/false) ask if has_ecommerce_physical = true : Will this site allow your customers to return products?
- [ ] `returns_address` (text) ask if has_returns = true : To what address will customers send their return products?
- [ ] `returns_limit` (text) ask if has_returns = true : How many days will this site allow for a returns period?
- [ ] `has_refund` (true/false) ask if has_ecommerce = true : Will this site offer refunds for unsatisfactory products or services?
- [ ] `refunds_limit` (text) ask if has_refunds = true : How many days after the product is shipped to the customer or downloaded by the customer will this site offer a refund?


###User-Generated Content and DMCA

- [x] `has_usergenerated` (true/false)  Will this site allow user-generated content of any kind?
- [x] `has_3p_content` (true/false) Has 3rd party content : Will this site allow users to add comments or content of any kind?
- [x] `has_DMCA_agent` (true/false) ask if has_3p_content = true : In the U.S., safe harbor protection from copyright liability for site content added by your users can be had by designating and registering with the Copyright Office a Digital Millenium Copyright Act agent for notice and takedown procedures. Will this site have a designated DMCA agent?
- [x] `DMCA_address` (text) ask if has_DMCA_agent = true : What will be the postal mailing address of your DMCA agent?
- [x] `DMCA_telephone` (text) ask if has_DMCA_agent = true : What will be the telephone number of your DMCA agent?
- [x] `DMCA_email` (text) ask if has_DMCA_agent = true : What will be the email address of your DMCA agent?


###Advertising

- [x] `has_advertising` (true/false) Will this site have advertisements?
- [x] `has_advertising_network` (true/false) ask if has_advertising = true : Will this site use a 3rd party network to supply advertising?
- [x] `has_advertising_adsense` (true/false) ask if has_advertising_network = true : Will this site use Google AdSense to supply advertising?


###Misc

- [x] `has_over18` (true/false) Will this site require visitors to be over the age of 18?
- [x] `has_arbitration` (true/false) Will this site require disputes to be privately arbitrated, as opposed to being litigated in court?
- [x] `has_SSL` (true/false) Will this site use SSL?
- [ ] `has_support_email` (true/false) Will this site offer support via email?
- [ ] `support_email` (text) ask if has_support_email = true : What will be the email address for support?
- [ ] `has_support_phone` (true/false) Will this site offer support via telephone?
- [ ] `support_phone` (text) ask if has_support_phone = true : What will be the telephone number for support?
- [x] `last_updated` auto generated date of documents when settings are saved
- [ ] `has_no_scrape` (true/false) Will this site prohibit the automatic collection of its data by others ("scraping")?
- [ ] `has_social` (true/false) Will this site include a social networking function, allowing users to connect with each other? ADD-ON TRIGGER
- [ ] `has_password` (true/false) Will any part of this site require a password for access?

submit data - bool
	survey - bool
	contact info - bool


- [x] integrated
