<?php
//--Invoice Title
    add_filter( 'wpo_wcpdf_document_title', 'wpo_wcpdf_invoice_title', 10, 2 );
    function wpo_wcpdf_invoice_title( $title, $document ) {
    	if ( 'invoice' === $document->get_type() ) {
    		$title = 'Order Details';	
    	}
    	return $title;
    }
    
//--Delivery Note
    add_filter( 'wpo_wcpdf_document_title', 'wpo_wcpdf_packing_slip_title', 10, 2 );
    function wpo_wcpdf_packing_slip_title( $title, $document ) {
    	if ( 'packing-slip' === $document->get_type() ) {
    		$title = 'Delivery Note';	
    	}
    	return $title;
    }