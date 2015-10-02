/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.bean;

import com.compreingressos.knowledge.model.EventoCliente;
import java.util.List;
import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.PersistenceContext;

/**
 *
 * @author edicarlos.barbosa
 */
@Stateless
public class EventoClienteFacade extends AbstractFacade<EventoCliente> {
    @PersistenceContext(unitName = "KnowledgePU")
    private EntityManager em;

    @Override
    protected EntityManager getEntityManager() {
        return em;
    }

    public EventoClienteFacade() {
        super(EventoCliente.class);
    }

    public List<EventoCliente> findAllByTask(Long id) {
        return getEntityManager().createNamedQuery("EventoCliente.findAllByTask").setParameter("idTask", id).getResultList();
    }

    public List<EventoCliente> findAllByProcess(long processInstanceId) {
        return getEntityManager().createNamedQuery("EventoCliente.findAllByProcess").setParameter("idProcess", processInstanceId).getResultList();
    }
    
    public List<EventoCliente> findAllByProcessCliente(long processInstanceId) {
        return getEntityManager().createNamedQuery("EventoCliente.findAllByProcessCliente").setParameter("idProcess", processInstanceId).getResultList();
    }
    
    public List<EventoCliente> findAllByProcessProdutor(long processInstanceId) {
        return getEntityManager().createNamedQuery("EventoCliente.findAllByProcessProdutor").setParameter("idProcess", processInstanceId).getResultList();
    }
}
