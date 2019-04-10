jQuery(document).ready($ => {
  const ramseyBatch = {};
  ramseyBatch.items = [];
  ramseyBatch.batchName;
  ramseyBatch.totalItems = ramseyBatch.items.length;
  ramseyBatch.currentItem = 0;
  ramseyBatch.itemsComplete = 0;
  const buttons = $('button[name="batchJobTrigger"]');
  let currentButton;
  let progressMeter;
  let progressBar;
  let statusMsg;

  /**
   * Update fetched items
   * @param {Array} items - Item ID's
   */
  function updateItems(items) {
    ramseyBatch.items = Object.values(items);
    ramseyBatch.totalItems = ramseyBatch.items.length;
  }

  /**
   * Update the progress bar status
   * @param  {int} current - The number of the current item.
   */
  function updateStatus(current) {
    const percentComplete = (current / ramseyBatch.totalItems) * 100;
    statusMsg.text(
      `Processing ${current} of ${ramseyBatch.totalItems} items...`
    );
    progressBar.css({
      width: `${percentComplete}%`
    });
  }

  /**
   * Complete the batch
   */
  function completeBatch() {
    statusMsg.html(
      `<strong>Done</strong>. Processed ${ramseyBatch.totalItems} of ${
        ramseyBatch.totalItems
      } items!`
    );
    console.log(
      `Batch is complete! Processed ${ramseyBatch.totalItems} of ${
        ramseyBatch.totalItems
      } items!`
    );
    ramseyBatch.items = [];
    ramseyBatch.totalItems = ramseyBatch.items.length;
    ramseyBatch.currentItem = 0;
    ramseyBatch.itemsComplete = 0;
    currentButton.prop("disabled", false);
  }

  /**
   * Process an individual item
   * @param  {mixed} item - Usually an item ID
   * @return {bool} - False if the AJAX call failed
   */
  function processItem(item) {
    return new Promise((resolve, reject) => {
      ramseyBatch.currentItem += 1;
      updateStatus(ramseyBatch.currentItem);

      console.log("Current item:", item);

      $.ajax({
        url: ajaxurl,
        method: "POST",
        data: {
          action: "ramsey-batch-item",
          item,
          batchName: ramseyBatch.batchName
        },
        success(response) {
          console.log(response);

          if (!response.success) {
            console.error(response.data.reason, response);

            return reject();
          }

          ramseyBatch.itemsComplete++;

          if ("warn" == response.data.type) {
            console.warn(response.data.reason, response);
          } else {
            console.log(response.data.reason, response);
          }

          return resolve();
        }
      });
    });
  }

  /**
   * Process queued items
   */
  function processBatchItems() {
    ramseyBatch.items
      .reduce(
        (promise, currentPromise, index) =>
          promise.then(() => processItem(currentPromise)),
        Promise.resolve()
      )
      .then(completeBatch);
  }

  /**
   * Collects form data into an array.
   *
   * @param NodeList formElements
   * @return Promise that resolves to array of data.
   */
  function getFormElementData(formElements) {
    let promiseChain = Promise.resolve();

    formElements.forEach(element => {
      promiseChain = promiseChain.then(data => {
        data = data === undefined ? [] : data;

        return new Promise((resolve, reject) => {
          if (element.type === "file") {
            let reader = new FileReader();

            reader.onload = function(e) {
              data.push({
                name: element.name,
                value: e.target.result
              });

              return resolve(data);
            };

            if (!element.files.length) {
              return resolve(data);
            }

            reader.readAsBinaryString(element.files[0]);
          } else {
            data.push({
              name: element.name,
              value: element.value
            });

            return resolve(data);
          }
        });
      });
    });

    return promiseChain;
  }

  /**
   * Get all the items we'll be working on
   * @param {Object} trigger - Triggering event
   * @return {Object} Promise object
   */
  function startBatch(trigger) {
    return new Promise((resolve, reject) => {
      currentButton = $(trigger);
      progressMeter.show();
      currentButton.prop("disabled", true);

      const formElements = currentButton.closest("tr").find("input,textarea");

      getFormElementData(formElements.get()).then(formElementData => {
        $.ajax({
          url: ajaxurl,
          method: "POST",
          data: {
            action: "ramsey-batch",
            batchName: ramseyBatch.batchName,
            formElementData: formElementData
          }
        }).done(response => {
          if (!response.success) {
            return reject(response.data.reason);
          }

          console.log(
            "Starting batch!",
            ramseyBatch.batchName,
            response.data.items
          );

          updateItems(response.data.items);

          if (!ramseyBatch.totalItems) {
            statusMsg.text("No items found.");
            currentButton.prop("disabled", false);
            progressBar.css({
              width: `100%`
            });

            return resolve("No items found.");
          }

          statusMsg.text(`Found ${ramseyBatch.totalItems} items...`);

          return resolve();
        });
      });
    });
  }

  // Handle the initial trigger
  $(buttons).on("click", e => {
    ramseyBatch.batchName = $(e.target).data("batchName");
    let cleanBatchName = ramseyBatch.batchName.replace(
      new RegExp("\\\\", "g"),
      ""
    );

    progressMeter = $(`tr.progressMeter[data-batch-name="${cleanBatchName}"]`);
    progressBar = progressMeter.find(".meter");
    progressBar.css({
      width: `0%`
    });
    statusMsg = progressMeter.find(".status");
    statusMsg.text("");

    startBatch(e.target).then(processBatchItems, response => {
      throw new Error(response);
    });
  });
});
