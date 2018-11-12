jQuery( document ).ready( ( $ ) => {
	const ramseyBatch = {};
	ramseyBatch.items = [];
	ramseyBatch.totalItems = ramseyBatch.items.length;
	ramseyBatch.currentItem = 0;
	ramseyBatch.itemsComplete = 0;
	const buttons = $( 'button[name="batchJobTrigger"]' );
	let currentButton;
	let progressMeter;
	let progressBar;
	let statusMsg;

	/**
	 * Update fetched items
	 * @param {Array} items - Item ID's
	 */
	function updateItems( items ) {
		ramseyBatch.items = Object.values( items );
		ramseyBatch.totalItems = ramseyBatch.items.length;
	}

	/**
	 * Update the progress bar status
	 * @param  {int} current - The number of the current item.
	 */
	function updateStatus( current ) {
		const percentComplete = current / ramseyBatch.totalItems * 100;
		statusMsg.text( `Processing ${current} of ${ramseyBatch.totalItems} items...` );
		progressBar.css( {
			width: `${percentComplete}%`
		} );
	}

	/**
	 * Complete the batch
	 */
	function completeBatch() {
		statusMsg.html( `<strong>Done</strong>. Processed ${ramseyBatch.totalItems} of ${ramseyBatch.totalItems} items!` );
		console.log( `Batch is complete! Processed ${ramseyBatch.totalItems} of ${ramseyBatch.totalItems} items!` );
		ramseyBatch.items = [];
		ramseyBatch.totalItems = ramseyBatch.items.length;
		ramseyBatch.currentItem = 0;
		ramseyBatch.itemsComplete = 0;
		currentButton.prop( 'disabled', false );
	}

	/**
	 * Process an individual item
	 * @param  {mixed} item - Usually an item ID
	 * @return {bool} - False if the AJAX call failed
	 */
	function processItem( item ) {
		return new Promise( ( resolve, reject ) => {
			ramseyBatch.currentItem += 1;
			updateStatus( ramseyBatch.currentItem );

			console.log( 'Current item:', item );

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'ramsey-batch-item',
					item
				},
				success( response ) {
					if ( ! response.success ) {
						console.log( response.data.reason, response );

						return reject();
					}

					ramseyBatch.itemsComplete++;

					console.log( response.data.reason, response );

					return resolve();
				}
			} );
		} );
	}

	/**
	 * Process queued items
	 */
	function processBatchItems() {
		ramseyBatch.items.reduce( ( promise, currentPromise, index ) => promise.then( () => processItem( currentPromise ) ), Promise.resolve() ).then( completeBatch );
	}

	/**
	 * Get all the items we'll be working on
	 * @param {Object} trigger - Triggering event
	 * @return {Object} Promise object
	 */
	function startBatch( trigger ) {
		return new Promise( ( resolve, reject ) => {
			currentButton = $( trigger );
			const batchName = currentButton.data( 'batchName' );
			const batchNameClean = batchName.replace( new RegExp( '\\\\', 'g' ), '' );

			progressMeter = $( `tr.progressMeter[data-batch-name="${batchNameClean}"]` );
			progressBar = progressMeter.find( '.meter' );
			statusMsg = progressMeter.find( '.status' );

			progressMeter.show();
			currentButton.prop( 'disabled', true );

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'ramsey-batch',
					batchName
				},
				success( response ) {
					if ( ! response.success ) {
						return reject( new Error( response.data.reason ) );
					}

					console.log( 'Starting batch!', response.data.items );

					updateItems( response.data.items );

					// No items found
					if ( ! ramseyBatch.totalItems ) {
						statusMsg.text( 'No items found.' );
						currentButton.prop( 'disabled', false );
						progressBar.css( {
							width: `100%`
						} );

						return resolve( 'No items found.' );
					}

					// Process each item
					statusMsg.text( `Found ${ramseyBatch.totalItems} items...` );

					resolve();
				}
			} );
		} );
	}

	// Handle the initial trigger
	$( buttons ).on( 'click', ( e ) => {
		startBatch( e.target ).then( processBatchItems, ( response ) => {
			throw new Error( 'Batch failed.' );
		} );
	} );
} );
