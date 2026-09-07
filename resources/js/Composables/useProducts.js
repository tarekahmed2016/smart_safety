import { router, useForm } from '@inertiajs/vue3'

export function useProducts() {
  const deleteForm = useForm({})

  const fetchNextOrdering = async () => {
    const response = await fetch(route('products.next-ordering'), {
      headers: { Accept: 'application/json' }
    })
    const data = await response.json()

    return data.ordering
  }

  const deleteProduct = (productId, callbacks = {}) => {
    return deleteForm.delete(route('products.destroy', productId), {
      preserveScroll: true,
      ...callbacks
    })
  }

  return {
    deleteForm,
    fetchNextOrdering,
    deleteProduct
  }
}
