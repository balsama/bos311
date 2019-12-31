# Boston 311 "Illegal Parking" Data

The City of Boston provides [datasets containing 311 Service Requests](https://data.boston.gov/dataset/311-service-requests).
However, those records don't include the "description" field from those requests. That's the field that I'm most
interested in.

The City broadly categorizes requests into "service groups" like "Illegal Parking". What I want to do is figure
out, for example, the number of cars illegally parked on sidewalks that were ticketed as opposed to the number of cars
taking up resident parking spots that were ticketed. But in order to do so, I need the description field to figure out
where the car was parked.

The City also provides a [rudimentary API that you can use to query the 311 database](https://mayors24.cityofboston.gov/open311). And this database _does_ include the
description field... 🎉! BUT! It doesn't allow you to search/filter on the description field. So it's still pretty
useless.

So [I went ahead and saved every page of every response from the API for "service group: Illegal Parking" locally](https://twitter.com/balsama/status/1211312725906907136). And now I can
query those results locally.

## Contents
### City of Boston 311 Data with Descriptions
This repo primarily contains the data that was scraped from the
[311 API](https://mayors24.cityofboston.gov/open311/v2/). You can find all of this data in json format in the 'data'
folder of this repo. It _only_ contains the 311 Requests for "Illegal Parking" - and only through 29 December 2019.

**THIS DATA WILL NOT BE KEPT UP TO DATE GOING FORWARD**

### Quick and dirty class to interact with the data.
My first project was to find and download all the pictures of cars parked on sidewalks. I'm not sure what I'll do with
them yet, but that's another thing. Probably something similar to [this](https://twitter.com/balsama/status/1210620138568982528). Anyway, to do so, I created `QueryBase`. You could use it to do something similar.

For example, if you wanted to download all photos of space savers:

```$php
include_once('src/QueryBase.php');
$query = new QueryBase();

$query->filterRecordsByDescription(['space', 'saver']);
$query->setImageUrls($query->getMatches(['media_url']));

$query->downloadImages();
```
