
## LYS Edebiyat Dataset

The dataset of the 'LYS Edebiyat Yazar & Kitap' iOS and Android games.

---

Both of the platforms are using the same data, and the iOS version is also able to update its local data from a **remote server**. This is why we needed a convenient way to process the **CSV dataset** with different dimensions, create *minified* and *beautified* versions of the datasets for production and development usages and update the **config JSON file** with the appropriate version. Therefore, we wrote a simple **PHP** script that **parses** the data, **generates** the JSON files and **updates** the config JSON with **appropriate file paths and build numbers**. With this script, we are able to trigger the data generation process by simply visiting the script's URL from the browser.

----

The dataset and the scripts are licensed under **MIT License.**