<template>
	<div>
        <ul>
            <li v-for="category in categories" class='admin-item'>

                <!-- Translated Category Title -->
                <span class="category">{{ getCategory(category.category) }}</span>

                <!-- List of tags in each category -->
                <span
                    v-for="tags in Object.entries(category.tags)"
                    v-html="getTags(tags, category.category)"
                    class="tag is-large is-info litter-tag"
                    @click="removeTag(category.category, tags[0])"
                />
            </li>
        </ul>
	</div>
</template>

<script>
/*** Tags (previously AddedItems) is quite similar to AdminItems except here we remove the tag, on AdminItems we reset the tag.*/
export default {
    name: 'Tags',
    computed: {

        /**
         * Categories from the tags object the user has created
         */
        categories ()
        {
            let categories = [];

            Object.entries(this.$store.state.litter.tags).map(entries => {
                if (Object.keys(entries[1]).length > 0)
                {
                    categories.push({
                        category: entries[0],
                        tags: entries[1]
                    });
                }
            });

            return categories;
        },
    },
    methods: {

        /**
         * Return translated value for category key
         */
        getCategory (category)
        {
            return this.$i18n.t('litter.categories.' + category);
        },

        /**
         * Return Translated key: value from tags[0]: tags[1]
         */
        getTags (tags, category)
        {
            return this.$i18n.t('litter.' + category + '.' + tags[0]) + ': ' + tags[1] + '<br>';
        },

        /**
         * Remove tag from this category
         * If all tags have been removed, delete the category
         */
        removeTag (category, tag_key)
        {
            this.$store.commit('removeTag', {
                category,
                tag_key
            });
        }
    }
}
</script>

<style scoped>

    .category {
        font-size: 1.25em;
        display: flex;
        justify-content: center;
        margin-bottom: 0.5em;
    }

    .litter-tag {
        cursor: pointer;
        margin-bottom: 10px;
        width: 100%;
    }

</style>
