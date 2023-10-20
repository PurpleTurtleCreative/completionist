
import {
	__experimentalItemGroup as ItemGroup,
	__experimentalItem as Item,
	SearchControl,
	Spinner,
} from '@wordpress/components';

import { decodeEntities } from '@wordpress/html-entities';

import { useSelect } from '@wordpress/data';
import { store as coreDataStore } from '@wordpress/core-data';

import { useState } from '@wordpress/element';

export default function PostSearchPicker({
	onChangeSelectedPost,
	onChangeQueryInput,
}) {
	const [ searchTerm, setSearchTerm ] = useState( '' );
	const selections = useSelect(
		select => {

			const query = {};
			if ( searchTerm ) {
				query.search = searchTerm;
				query.per_page = 5;
				// Limiting the returned fields sadly isn't working
				// despite source code: https://github.com/WordPress/gutenberg/blob/33fabf5ceba1be77dcf8e453bde59b64b7d862e0/packages/core-data/src/resolvers.js#L211-L225
				query._fields = 'id,title';
			}

			const postTypes = select( coreDataStore ).getPostTypes({ per_page: -1 })?.filter(({ viewable }) => !! viewable);

			window.console.log('postTypes', postTypes);

			const posts = [];
			let hasResolved = select( coreDataStore ).hasFinishedResolution( 'getPostTypes', { per_page: -1 } );

			if ( postTypes?.length ) {
				hasResolved = true; // True until found false.
				for ( const { slug } of postTypes ) {
					const selectorArgs = [ 'postType', slug, query ];
					const selectedRecords = select( coreDataStore ).getEntityRecords( ...selectorArgs );
					hasResolved = ( hasResolved && select( coreDataStore ).hasFinishedResolution( 'getEntityRecords', selectorArgs ) );
					if ( selectedRecords?.length ) {
						posts.push(...selectedRecords);
					}
				}
			}

			return { hasResolved, posts, postTypes };
		},
		[ searchTerm ]
	);

	window.console.log( selections );
	const { hasResolved, posts, postTypes } = selections;

	let postsList = <Spinner />
	if ( ! searchTerm?.length ) {
		postsList = null;
	} else if ( hasResolved ) {
		if ( ! posts?.length ) {
			postsList = <p>No results</p>;
		} else {
			window.console.log(posts);
			postsList = <p>{`${posts.length} Results`}</p>;
			// postsList = (
			// 	<ItemGroup>
			// 		{ posts.map(p => <Item key={ p.id }>{ decodeEntities( p.title.rendered ) }</Item>) }
			// 	</ItemGroup>
			// );
		}
	}

	return (
		<div className="ptc-PostSearchPicker">
			<SearchControl onChange={ setSearchTerm } value={ searchTerm } />
			{ postsList }
		</div>
	);
}
