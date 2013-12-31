febric
=======

A purified framework for web &amp; mobile development, which allow us to **febricate** software products quickly but also have good quality. Named after **Februus**, the Roman God of Purification. (also the origin of Febrary)

The project eventually will support as many languages as possible. The first Language supported is PHP, which is called **phebric**.

The philosophy of **febric** is:
* Don't reinvent the wheel, utilize all the existing solutions.
* Keep It Simple & Stupid
* Easy to learn/adopt/migrate
* IDE integrated total solution
* Compatible with other frameworks
* Cross-Platform
* Multilingual
* SaaS oriented design
* Auto Documentation
* Self Validation
* Follow all the recent or future standards
* Design your software with a fully-functional-mockup

Let's see how easy a **febric** can be:

    class api extends Febric {
        public function getItem() {
            $item_id = get('item_id', RE_NUM);              // $item_id must be a natural number
            return getRow('item', [item_id' => $item_id]);  // fetch data from database
        }
    }

We will get the following JSON by calling http://domain.name.or.ip/api/getItem?item_id=123

    {"item_id":"123","kind_id":"2","name":"Coffee","is_hot":"0","price":"35","error":0,"message":"Success"}

This project is quite immature and under construction, so the information and documents may not be sufficient. Please take a look at html/example1/ and html/hankyu/ to see how it works.

Sponsored by Richi Inc.
