import React from 'react'
import useAuth from '../utilities/hooks/useAuth'

function Dashboard() {

    const { logged_in, username, user_type } = useAuth()

    return (
        <>
            <div>Dashboard</div>
        </>

    )
}

export default Dashboard