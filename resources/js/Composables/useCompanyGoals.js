import { router, useForm } from '@inertiajs/vue3'

export function useCompanyGoals() {
  const deleteForm = useForm({})

  const fetchNextOrdering = async () => {
    const response = await fetch(route('company-goals.next-ordering'), {
      headers: { Accept: 'application/json' },
    })

    if (!response.ok) {
      return null
    }

    try {
      const data = await response.json()

      return data.ordering ?? null
    } catch {
      return null
    }
  }

  const deleteCompanyGoal = (companyGoalId, callbacks = {}) => {
    return deleteForm.delete(route('company-goals.destroy', companyGoalId), {
      preserveScroll: true,
      ...callbacks,
    })
  }

  return {
    deleteForm,
    fetchNextOrdering,
    deleteCompanyGoal,
  }
}
